<?php
namespace minepark\player;

use minepark\Api;
use minepark\Core;
use minepark\Permission;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\EffectInstance;

class Damager
{
    public const POINT_AFTER_KILL = 'палата';

    private $config;
    private $reasons;

    public function __construct()
	{
        $this->config = new Config($this->getCore()->getTargetDirectory() . "nopvp.json", Config::JSON);
        $this->reasons = array("сотрясения мозга", "потери сознания", "ряда переломов");
	}

	public function getCore() : Core
	{
		return Core::getActive();
    }
    
    public function getConfig() : Config
	{
		return $this->config;
	}

    public function kick(Player $player, Player $damager) : bool
    {
        if($damager->org == 4 and $damager->getInventory()->getItemInHand()->getName() == "Stick") {
            $this->getCore()->getChatter()->send($damager, "§8(§dв руках дубинка-электрошокер§8)", "§d : ", 10);
            $this->getCore()->getChatter()->send($player, "§8(§dлежит на полу | ослеплен§8)", "§d : ", 12);
            
            $this->getCore()->getApi()->changeAttr($player, Api::ATTRIBUTE_WANTED);

            $player->setImmobile(true);
            $player->bar = "§6ВЫ ОГЛУШЕНЫ!";

            return false;
        }
        
        return $this->checkPvp($player, $damager);
    }

    public function kill(Player $player, ?Entity $damager) : bool
    {
        $this->getCore()->getMapper()->teleportPoint($player, self::POINT_AFTER_KILL);

        $player->addEffect(new EffectInstance(Effect::getEffect(2), 5000, 1));
        $player->addEffect(new EffectInstance(Effect::getEffect(18), 5000, 1));
        $player->addEffect(new EffectInstance(Effect::getEffect(19), 5000, 1));
        $player->setHealth(4);

        $player->sendMessage("§6Вы очнулись после ". $this->getRandomReason() . ".");
        $player->sendMessage("§6Срочно найдите доктора!");
        $player->sendMessage("§dЕсли на Вас напали, вызовите полицию: /c 02");

        $player->sleepOn($player->getPosition()->subtract(0, 1, 0));
        
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
            if($p->hasPermission(Permission::ADMINISTRATOR)) {
                if($damager instanceof Player) {
                    $p->sendMessage("§7[§6!§7] PvP : §c" . $damager->getName() . " убил " . $player->getName());
                } else {
                    $p->sendMessage("§7[§6!§7] Kill : §c"." игрок  " . $player->getName()." убился..");
                }
            }
        }

        return true;
    }

    private function checkPvp(Player $player, Player $damager) : bool
    {
        if($damager->nopvp) {
            $damager->sendMessage("§6PvP режим недоступен!");
            return true;
        }
        
        foreach($this->getConfig()->getAll(true) as $name) {
            $x1 = $this->getConfig()->getNested("$name.pos1.x");
            $y1 = $this->getConfig()->getNested("$name.pos1.y");
            $z1 = $this->getConfig()->getNested("$name.pos1.z");
            $x2 = $this->getConfig()->getNested("$name.pos2.x");
            $y2 = $this->getConfig()->getNested("$name.pos2.y");
            $z2 = $this->getConfig()->getNested("$name.pos2.z");

            $x = floor($player->getX());
            $y = floor($player->getY());
            $z = floor($player->getZ());

            if($this->getCore()->getApi()->interval($x,$x1,$x2) 
                and $this->getCore()->getApi()->interval($y,$y1,$y2) 
                    and $this->getCore()->getApi()->interval($z,$z1,$z2)) {
                $damager->sendMessage("§aВы находитесь в зеленой зоне! Здесь безопасно!");
                return true;
            }
        }

        return false;
    }

    private function getRandomReason() : string
    {
        return $this->reasons[mt_rand(0, count($this->reasons) - 1)];
    }
}
?>
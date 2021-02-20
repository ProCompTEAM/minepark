<?php
namespace minepark\player;

use minepark\Api;
use minepark\Core;
use minepark\defaults\Permissions;
use minepark\player\implementations\MineParkPlayer;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\EffectInstance;

class Damager
{

    private $config;
    private $reasons;

    public function __construct()
	{
        $this->config = new Config($this->getCore()->getTargetDirectory() . "greenZones.json", Config::JSON);
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

    public function kick(MineParkPlayer $player, MineParkPlayer $damager) : bool
    {
        if($damager->getProfile()->organisation == 4 and $damager->getInventory()->getItemInHand()->getName() == "Stick") {
            $this->getCore()->getChatter()->send($damager, "§8(§dв руках дубинка-электрошокер§8)", "§d : ", 10);
            $this->getCore()->getChatter()->send($player, "§8(§dлежит на полу | ослеплен§8)", "§d : ", 12);
            
            $this->getCore()->getApi()->changeAttr($player, Api::ATTRIBUTE_WANTED);

            $player->setImmobile(true);
            $player->getStatesMap()->bar = "§6ВЫ ОГЛУШЕНЫ!";

            return false;
        }
        
        return $this->checkPvp($player, $damager);
    }

    public function kill(MineParkPlayer $player, ?Entity $damager) : bool
    {
        $this->getCore()->getMapper()->teleportPoint($player, Mapper::POINT_NAME_HOSPITAL);

        $player->addEffect(new EffectInstance(Effect::getEffect(2), 5000, 1));
        $player->addEffect(new EffectInstance(Effect::getEffect(18), 5000, 1));
        $player->addEffect(new EffectInstance(Effect::getEffect(19), 5000, 1));
        $player->setHealth(4);

        $player->sendMessage("§6Вы очнулись после ". $this->getRandomReason() . ".");
        $player->sendMessage("§6Срочно найдите доктора!");
        $player->sendMessage("§dЕсли на Вас напали, вызовите полицию: /c 02");

        $player->sleepOn($player->getPosition()->subtract(0, 1, 0));
        
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
            if($onlinePlayer->hasPermission(Permissions::ADMINISTRATOR)) {
                if($damager instanceof MineParkPlayer) {
                    $onlinePlayer->sendMessage("§7[§6!§7] PvP : §c" . $damager->getName() . " убил " . $player->getName());
                } else {
                    $onlinePlayer->sendMessage("§7[§6!§7] Kill : §c"." игрок  " . $player->getName()." умер..");
                }
            }
        }

        return true;
    }

    private function checkPvp(MineParkPlayer $player, MineParkPlayer $damager) : bool
    {
        if($damager->getStatesMap()->damageDisabled) {
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
<?php
namespace minepark\components;

use minepark\Api;
use minepark\Providers;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use minepark\defaults\Permissions;
use pocketmine\entity\EffectInstance;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\organisations\Organisations;
use minepark\defaults\EventList;
use minepark\defaults\MapConstants;
use minepark\Events;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\GameMode;

class Damager extends Component
{
    private Config $config;

    private array $reasons;

    private GameChat $gameChat;

    public function initialize()
    {
        $this->gameChat = Components::getComponent(GameChat::class);

        Events::registerEvent(EventList::ENTITY_DAMAGE_EVENT, [$this, "processEntityDamageEvent"]);

        $this->config = new Config($this->getCore()->getTargetDirectory() . "greenZones.json", Config::JSON);
        $this->reasons = array("сотрясения мозга", "потери сознания", "ряда переломов");
    }

    public function getAttributes() : array
    {
        return [
        ];
    }
    
    public function getConfig() : Config
    {
        return $this->config;
    }

    public function processEntityDamageEvent(EntityDamageEvent $event)
    {
        if (!$event->getEntity() instanceof MineParkPlayer) {
            return;
        }

        $damager = null;

        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();

            $this->checkForStunning($event);
        }

        if ($event->isCancelled()) {
            return;
        }

        $this->checkForPlayerKilling($event->getFinalDamage(), $event->getEntity(), $damager);
    }

    private function checkForStunning(EntityDamageByEntityEvent $event)
    {
        if (!$event->getDamager() instanceof MineParkPlayer or !$event->getEntity() instanceof MineParkPlayer) {
            return;
        }

        $damager = MineParkPlayer::cast($event->getDamager());
        $player = MineParkPlayer::cast($event->getEntity());

        if ($damager->getProfile()->organisation === Organisations::SECURITY_WORK and $damager->getInventory()->getItemInHand()->getId() === Item::STICK) {
            $this->processStunAction($player, $damager);
        }
        
        $event->setCancelled($this->canPlayerHurt($player, $damager));
    }

    private function checkForPlayerKilling(int $finalDamage, MineParkPlayer $victim, ?Entity $damager)
    {
        if ($victim->getHealth() > $finalDamage) {
            return;
        }

        Providers::getMapProvider()->teleportPoint($victim, MapConstants::POINT_NAME_HOSPITAL);

        $victim->addEffect(new EffectInstance(Effect::getEffect(2), 5000, 1));
        $victim->addEffect(new EffectInstance(Effect::getEffect(18), 5000, 1));
        $victim->addEffect(new EffectInstance(Effect::getEffect(19), 5000, 1));
        $victim->setHealth(4);

        $victim->sendMessage("§6Вы очнулись после ". $this->getRandomDeathReason() . ".");
        $victim->sendMessage("§6Срочно найдите доктора!");
        $victim->sendMessage("§dЕсли на Вас напали, вызовите полицию: /c 02");

        $victim->sleepOn($victim->getPosition()->subtract(0, 1, 0));

        $this->broadcastPlayerDeath($victim, $damager);
    }

    private function processStunAction(MineParkPlayer $victim, MineParkPlayer $policeMan)
    {
        $this->gameChat->sendLocalMessage($policeMan, "§8(§dв руках дубинка-электрошокер§8)", "§d : ", 10);
        $this->gameChat->sendLocalMessage($victim, "§8(§dлежит на полу | ослеплен§8)", "§d : ", 12);
        
        $this->getCore()->getApi()->changeAttr($victim, Api::ATTRIBUTE_WANTED);

        $victim->setImmobile(true);
        $victim->getStatesMap()->bar = "§6ВЫ ОГЛУШЕНЫ!";
    }

    private function broadcastPlayerDeath(MineParkPlayer $victim, ?Entity $damager)
    {
        if (isset($damager) and $damager instanceof MineParkPlayer) {
            $message = "§7[§6!§7] PvP : §c" . $damager->getName() . " убил " . $victim->getName();
        } else {
            $message = "§7[§6!§7] Kill : §c"." игрок  " . $victim->getName()." умер..";
        }

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
            $onlinePlayer = MineParkPlayer::cast($onlinePlayer);

            if ($onlinePlayer->isAdministrator()) {
                $onlinePlayer->sendMessage($message);
            }
        }
    }

    private function canPlayerHurt(MineParkPlayer $player, MineParkPlayer $damager) : bool
    {
        if ($damager->getStatesMap()->damageDisabled) {
            $damager->sendMessage("§6PvP режим недоступен!");
            return true;
        }
        
        foreach($this->getConfig()->getAll() as $name) {
            $x1 = $this->getConfig()->getNested("$name.pos1.x");
            $y1 = $this->getConfig()->getNested("$name.pos1.y");
            $z1 = $this->getConfig()->getNested("$name.pos1.z");
            $x2 = $this->getConfig()->getNested("$name.pos2.x");
            $y2 = $this->getConfig()->getNested("$name.pos2.y");
            $z2 = $this->getConfig()->getNested("$name.pos2.z");

            $x = floor($player->getX());
            $y = floor($player->getY());
            $z = floor($player->getZ());

            if($this->getCore()->getApi()->interval($x ,$x1, $x2) 
                and $this->getCore()->getApi()->interval($y, $y1, $y2) 
                    and $this->getCore()->getApi()->interval($z, $z1 , $z2)) {
                $damager->sendMessage("§aВы находитесь в зеленой зоне! Здесь нельзя бить игроков!");
                return true;
            }
        }

        return false;
    }

    private function getRandomDeathReason() : string
    {
        return $this->reasons[mt_rand(0, count($this->reasons) - 1)];
    }
}
?>
<?php
namespace minepark\components\settings;

use minepark\Events;
use minepark\Providers;
use minepark\Components;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use minepark\utils\MathUtility;
use minepark\defaults\EventList;
use minepark\components\chat\Chat;
use minepark\defaults\MapConstants;
use pocketmine\entity\EffectInstance;
use minepark\components\base\Component;
use minepark\defaults\PlayerAttributes;
use minepark\common\player\MineParkPlayer;
use pocketmine\event\entity\EntityDamageEvent;
use minepark\components\organisations\Organisations;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EntitySettings extends Component
{
    private Config $config;

    private array $reasons;

    private Chat $gameChat;

    public function initialize()
    {
        $this->gameChat = Components::getComponent(Chat::class);

        Events::registerEvent(EventList::ENTITY_DAMAGE_EVENT, [$this, "processEntityDamageEvent"]);

        $this->config = new Config($this->getCore()->getTargetDirectory() . "greenZones.json", Config::JSON); //TODO: INTO MDC
        $this->reasons = array("сотрясения мозга", "потери сознания", "ряда переломов");
    }

    public function getAttributes() : array
    {
        return [
        ];
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
        
        $victim->changeAttribute(PlayerAttributes::WANTED);

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

        foreach($this->getServer()->getOnlinePlayers() as $onlinePlayer) {
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
        
        foreach($this->config->getAll() as $name) {
            $x1 = $this->config->getNested("$name.pos1.x");
            $y1 = $this->config->getNested("$name.pos1.y");
            $z1 = $this->config->getNested("$name.pos1.z");
            $x2 = $this->config->getNested("$name.pos2.x");
            $y2 = $this->config->getNested("$name.pos2.y");
            $z2 = $this->config->getNested("$name.pos2.z");

            $x = floor($player->getX());
            $y = floor($player->getY());
            $z = floor($player->getZ());

            if(MathUtility::interval($x ,$x1, $x2) 
                and MathUtility::interval($y, $y1, $y2) 
                    and MathUtility::interval($z, $z1 , $z2)) {
                $damager->sendMessage("§aВы находитесь в зеленой зоне! Здесь запрещено драться!");
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
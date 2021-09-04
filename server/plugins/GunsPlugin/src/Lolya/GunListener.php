<?php
namespace Lolya;

use pocketmine\player\Player;
use Lolya\utils\CallbackTask;
use pocketmine\event\Listener;

use Lolya\creature\BulletEntity;
use pocketmine\event\player\PlayerInteractEvent;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\entity\ProjectileHitEntityEvent;

class GunListener implements Listener
{
    public $main;
    public $playersCanShoot;

    public function __construct($mainClass)
    {
        $this->main = $mainClass;
        $this->playersCanShoot = array();
    }
    
    public function onInteract(PlayerInteractEvent $event)
    {
        $player = MineParkPlayer::cast($event->getPlayer());

        if(!$player->getStatesMap()->authorized) {
            return;
        }
        
        if (!$this->playerCanShoot($player)) {
            return;
        }

        $gun = $this->main->getWeaponInHand($player);
        if (!$gun) {
            return;
        }
        
        $gunData = $this->main->getWeaponInfo($gun->getId());
        
        if (!$gunData) {
            return;
        }
        
        if (!$player->isCreative()) {
            if (!$this->hasAmmo($player, $gunData['name'])) {
                return;
            }
        }
        
        $this->makeNotShoot($player);

        $player->sendSound($gunData['sound']);
        
        foreach($this->main->getServer()->getOnlinePlayers() as $plr) {
            if ($player->getName() == $plr->getName()) {
                continue;
            }

            if($player->getLocation()->distance($plr->getLocation()) < 10) {
                $plr->sendSound($gunData['sound']);
            }
        }
        
        $this->main->shoot->execute($player, $gunData['damage']);
        
        if ($gunData['burst'] !== "AUTO")
        {
            $this->main->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "makeShoot"], array($player)), $gunData['burst']);
        } else {
            $this->makeShoot($player);
        }
    }
    
    public function hasAmmo($player, $weaponName)
    {
        foreach($player->getInventory()->getContents() as $item)
        {
            if ($item->getId() != 262 or !$item->hasCustomName()) {
                continue;
            }
            
            $ammoName = substr($item->getName(), 33);
            
            if ($weaponName != $ammoName) {
                continue;
            }
            
            $item->setCount(1);
            $player->getInventory()->removeItem($item); 

            return true;
        }
        return false;
    }
    
    public function makeShoot($player)
    {
        $plrId = $this->getPlayerID($player);
        
        $this->playersCanShoot[$plrId] = true;
    }
    
    public function makeNotShoot($player)
    {
        $plrId = $this->getPlayerID($player);
        $this->playersCanShoot[$plrId] = false;
    }

    public function playerCanShoot($player)
    {
        $plrId = $this->getPlayerID($player);
        
        if (!isset($this->playersCanShoot[$plrId]))
        {
            $this->playersCanShoot[$plrId] = true;
            
            return true;
        }
        
        if ($this->playersCanShoot[$plrId] == false) {
            return false;
        } else {
            return true;
        }
    }
    
    public function getPlayerID($player)
    {
        return strtolower($player->getName());
    }
    
    public function onHit(ProjectileHitEntityEvent $event)
    {
        $player = $event->getEntityHit();
        $entity = $event->getEntity();

        if (!$player instanceof Player or !$entity instanceof BulletEntity) {
            return;
        }

        if ($player->isCreative()) {
            return;
        }
        
        $player->setHealth($player->getHealth() - $entity->getAmmoDamage());
    }
}
?>
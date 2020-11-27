<?php
namespace minepark\player;

use minepark\Core;
use minepark\Profiler;
use pocketmine\Player;
use minepark\Permissions;
use pocketmine\item\Item;
use minepark\modules\organisations\Organisations;

class Initializer
{
    public const DEFAULT_MONEY_PRESENT = 1000;

    public const DONATER_STATUS_BONUS_COUNT = 100;

    public function getCore() : Core
    {
        return Core::getActive();
    }

    public function getProfiler() : Profiler
    {
        return Core::getActive()->getProfiler();
    }
    
    public function initialize(Player $player)
    {
        $player->isnew = $this->getProfiler()->isNewPlayer($player);

        $this->getProfiler()->initializeProfile($player);
        
        $this->setDefaults($player);

        $this->setPermissions($player);
        
        $this->showLang($player);
    }

    public function join(Player $player)
	{
		$player->removeAllEffects();
        $player->setNameTag("");

        if($player->isnew) {
			$this->handleNewPlayer($player);
        }

        $this->showDonaterStatus($player);
        
        $this->addInventoryItems($player);
    }

    public function checkInventoryItems(Player $player)
	{
        $itemId = $player->getInventory()->getItemInHand()->getId();

        //CHECK ITEMS DEFAULT KIT
		if($itemId == 336) { //336 - phone
			$player->sendCommand("/c");
		}

		if($itemId == 340) { //340 - passport
			$player->sendCommand("/doc");
		}

		if($itemId == 405) { //405 - gps
			$player->sendCommand("/gps");
		}
	}

    private function addInventoryItems(Player $player)
	{
        //GIVING ITEMS DEFAULT KIT
		$phone = Item::get(336, 0, 1);
        $phone->setCustomName("Телефон");
        
		$passport = Item::get(340, 0, 1);
        $passport->setCustomName("Паспорт");
        
		$gps = Item::get(405, 0, 1);
		$gps->setCustomName("Навигатор");
		
		if(!$player->getInventory()->contains($phone)) {
            $player->getInventory()->setItem(0, $phone);
        }
		
		if(!$player->getInventory()->contains($passport)) {
            $player->getInventory()->setItem(1, $passport);
        }
		
		if(!$player->getInventory()->contains($gps)) {
            $player->getInventory()->setItem(2, $gps);
        }

        if($player->getProfile()->organisation == Organisations::SECURITY_WORK) {
			$item = Item::get(280, 0, 1);
			$player->getInventory()->addItem($item);
        }
    }

    private function setDefaults(Player $player)
	{
        $player->auth = false;
        
        $player->gps = null;
        $player->bar = null;

        $player->phoneRcv = null;
        $player->phoneReq = null;

        $player->goods = array();

        $player->wbox = null;

        $player->nopvp = false;
        
        $player->lastTap = time();
    }
    
    private function showLang(Player $player)
    {
        $message = "Selected locale: " . $player->locale;
        $this->getCore()->getServer()->getLogger()->info($message);
    }

    private function handleNewPlayer(Player $player)
	{
        $this->getCore()->getBank()->givePlayerMoney($player, self::DEFAULT_MONEY_PRESENT);
		$this->getCore()->getTrackerModule()->enableTrack($player);
    }
    
    private function setPermissions(Player $player)
	{
        $player->addAttachment($this->getCore(), Permissions::ANYBODY, true);

        $this->addCustomPermissions($player);
    }
    
    private function showDonaterStatus(Player $donater)
	{
        if(!$donater->hasPermission("group.custom")){
            return;
        }
        
        $label = $this->getDonaterLabel($donater);

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player->addTitle("", $label . " " . $donater->getName() . "{UserOnline}");
        }
    }
    
    private function getDonaterLabel(Player $donater)
	{
        if($donater->isOp()) {
            return "§7⚑РУКОВОДСТВО ПАРКА";
        }

        $profile = $donater->getProfile();

        if($profile->administrator) {
            if($profile->builder) {
                return "§bСтроитель парка";
            } elseif($profile->realtor) {
                return "§cРиэлтор недвижимости";
            } else {
                return "§aАдминистратор";
            }
        }

        if($profile->vip) {
            return "§e§0-=§9V.I.P§0=-§e";
        }

        if($profile->bonus > self::DONATER_STATUS_BONUS_COUNT) {
            return "§7~§6§e-=ДоНаТеР=-§6§7~";
        }
    }

    private function addCustomPermissions(Player $player)
    {
        $profile = $player->getProfile();

        $hasCustomPermissions = false;

        if(!is_null($profile->group)) {
            $permission = "group." . strtolower($profile->group);
            $player->addAttachment($this->getCore(), $permission, true);
            $hasCustomPermissions = true;
        }

        if($profile->vip) {
            $player->addAttachment($this->getCore(), Permissions::VIP, true);
            $this->addVipPermissions($player);
            $hasCustomPermissions = true;
        }

        if($profile->administrator) {
            $player->addAttachment($this->getCore(), Permissions::ADMINISTRATOR, true);
            $this->addAdministratorPermissions($player);
            $hasCustomPermissions = true;
        }

        if($profile->builder) {
            $player->addAttachment($this->getCore(), Permissions::BUILDER, true);
            $this->addBuilderPermissions($player);
            $hasCustomPermissions = true;
        }

        if($profile->realtor) {
            $player->addAttachment($this->getCore(), Permissions::REALTOR, true);
            $this->addRealtorPermissions($player);
            $hasCustomPermissions = true;
        }

        if($player->isOp()) {
            $player->addAttachment($this->getCore(), Permissions::OPERATOR, true);
            $hasCustomPermissions = true;
        }

        if($hasCustomPermissions) {
            $player->addAttachment($this->getCore(), Permissions::CUSTOM, true);
		}
    }
    
    private function addAdministratorPermissions(Player $player)
    {
        $permissions = Permissions::getCustomAdministratorPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addBuilderPermissions(Player $player)
    {
        $permissions = Permissions::getCustomBuilderPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addRealtorPermissions(Player $player)
    {
        $permissions = Permissions::getCustomRealtorPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addVipPermissions(Player $player)
    {
        $permissions = Permissions::getCustomVipPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }
}

?>
<?php
namespace minepark\components;

use minepark\Core;
use minepark\Profiler;
use minepark\Providers;
use pocketmine\item\Item;
use minepark\defaults\Permissions;
use minepark\defaults\PaymentMethods;
use minepark\models\player\StatesMap;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;

class Initializer extends Component
{
    public const DEFAULT_MONEY_PRESENT = 1000;

    public const DONATER_STATUS_BONUS_COUNT = 100;

    public const MINIMAL_SKILL_MINUTES_PLAYED = 60;

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function getProfiler() : Profiler
    {
        return Core::getActive()->getProfiler();
    }
    
    public function initialize(MineParkPlayer $player)
    {
        $this->setDefaults($player);

        $this->updateNewPlayerStatus($player);

        $this->getProfiler()->initializeProfile($player);

        $this->updateBegginerStatus($player);

        $this->setPermissions($player);
        
        $this->showLang($player);
    }

    public function join(MineParkPlayer $player)
	{
		$player->removeAllEffects();
        $player->setNameTag("");

        if($player->getStatesMap()->isNew) {
			$this->handleNewPlayer($player);
        }

        Providers::getBankingProvider()->initializePlayerPaymentMethod($player);

        $this->showDonaterStatus($player);
        
        $this->addInventoryItems($player);
    }

    public function checkInventoryItems(MineParkPlayer $player)
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

    private function addInventoryItems(MineParkPlayer $player)
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

    private function setDefaults(MineParkPlayer $player)
	{
        $statesMap = new StatesMap();

        $statesMap->auth = false;
        $statesMap->isNew = false;
        $statesMap->isBeginner = false;

        $statesMap->gpsLightsVisible = false;
        
        $statesMap->gps = null;
        $statesMap->bar = null;

        $statesMap->phoneRcv = null;
        $statesMap->phoneReq = null;

        $statesMap->goods = array();

        $statesMap->loadWeight = null;

        $statesMap->damageDisabled = false;

        $statesMap->lastTap = time();

        $statesMap->paymentMethod = PaymentMethods::CASH;

        $statesMap->ridingVehicle = null;
        $statesMap->rentedVehicle = null;

        $statesMap->bossBarSession = null;

        $player->setStatesMap($statesMap);
    }

    private function updateNewPlayerStatus(MineParkPlayer $player) 
    {
        $status = $this->getProfiler()->isNewPlayer($player);
        $player->getStatesMap()->isNew = $status;
    }

    private function updateBegginerStatus(MineParkPlayer $player) 
    {
        $status = $player->getStatesMap()->isNew or 
            $player->getProfile()->minutesPlayed < self::MINIMAL_SKILL_MINUTES_PLAYED;
        $player->getStatesMap()->isBeginner = $status;
    }
    
    private function showLang(MineParkPlayer $player)
    {
        $message = "Selected locale: " . $player->locale;
        $this->getCore()->getServer()->getLogger()->info($message);
    }

    private function handleNewPlayer(MineParkPlayer $player)
	{
        Providers::getBankingProvider()->givePlayerMoney($player, self::DEFAULT_MONEY_PRESENT);
        $this->getCore()->getTrackerModule()->enableTrack($player);
        $this->presentNewPlayer($player);
    }

    private function presentNewPlayer(MineParkPlayer $newPlayer)
	{
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player->sendTitle("§6" . $newPlayer->getName(), "§aВ парке новый посетитель!", 5);
        }
    }
    
    private function setPermissions(MineParkPlayer $player)
	{
        $player->addAttachment($this->getCore(), Permissions::ANYBODY, true);

        $this->addCustomPermissions($player);
    }
    
    private function showDonaterStatus(MineParkPlayer $donater)
	{
        if(!$donater->hasPermission("group.custom")){
            return;
        }
        
        $label = $this->getDonaterLabel($donater);

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            $player->addLocalizedTitle("§e" . $donater->getName(), $label . " " . $donater->getName() . " {UserOnline}", 5);
        }
    }
    
    private function getDonaterLabel(MineParkPlayer $donater)
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

    private function addCustomPermissions(MineParkPlayer $player)
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
    
    private function addAdministratorPermissions(MineParkPlayer $player)
    {
        $permissions = Permissions::getCustomAdministratorPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addBuilderPermissions(MineParkPlayer $player)
    {
        $permissions = Permissions::getCustomBuilderPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addRealtorPermissions(MineParkPlayer $player)
    {
        $permissions = Permissions::getCustomRealtorPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }

    private function addVipPermissions(MineParkPlayer $player)
    {
        $permissions = Permissions::getCustomVipPermissions();

        foreach($permissions as $permission) {
            $player->addAttachment($this->getCore(), $permission, true);
        }
    }
}

?>
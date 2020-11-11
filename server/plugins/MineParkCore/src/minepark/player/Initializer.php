<?php
namespace minepark\player;

use minepark\Core;
use minepark\mdc\sources\UsersSource;
use minepark\modules\organisations\Organisations;
use pocketmine\Player;
use pocketmine\item\Item;

class Initializer
{
    public const DEFAULT_MONEY_PRESENT = 1000;

    public function getCore() : Core
    {
        return Core::getActive();
    }

    public function getRemoteSource() : UsersSource
    {
        return $this->getCore()->getMDC()->getSource("users");
    }
    
    public function initialize(Player $player)
    {
        $playerName = $player->getName();
        
        $player->isnew = !$this->getRemoteSource()->isUserExist($playerName);
        
        $this->loadProfile($player);
        
        $player->auth = false;

        $player->gps = null;
        $player->bar = null;

		$player->phoneRcv = null;
		$player->phoneReq = null;	
		$player->goods = array();
		$player->wbox = null;
		$player->nopvp = false;
        $player->lastTap = time();
        
        $this->showLang($player);
    }

    public function join(Player $player)
	{
		$player->removeAllEffects();
        $player->setNameTag("");

        if(!$player->isnew) {
			$this->handleNewPlayer($player);
        }
        
        $this->addInventoryItems($player);
        
		if($player->getProfile()->organisation == Organisations::SECURITY_WORK) {
			$item = Item::get(280, 0, 1);
			$player->getInventory()->addItem($item);
        }
    }

    private function addInventoryItems(Player $player)
	{
        //GIVING ITEMS DEFAULT KIT
		$phone = Item::get(336, 0, 1); //336 - phone
        $phone->setCustomName("Телефон");
        
		$passport = Item::get(340, 0, 1); //340 - passport
        $passport->setCustomName("Паспорт");
        
		$gps = Item::get(405, 0, 1); //405 - gps
		$gps->setCustomName("Навигатор");
		
		if(!$player->getInventory()->contains($phone)) {
            $player->getInventory()->setItem(0,$phone);
        }
		
		if(!$player->getInventory()->contains($passport)) {
            $player->getInventory()->setItem(1,$passport);
        }
		
		if(!$player->getInventory()->contains($gps)) {
            $player->getInventory()->setItem(2,$gps);
        }
    }
    
    private function loadProfile(Player $player)
	{
        if($player->isnew) {
            $player->profile = $this->getRemoteSource()->createUserInternal($player->getName());
		} else {
            $player->profile = $this->getRemoteSource()->getUser($player->getName());
        }
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
}

?>
<?php
namespace minepark\player;

use minepark\Core;
use minepark\Profiler;
use pocketmine\Player;
use pocketmine\item\Item;
use minepark\modules\organisations\Organisations;

class Initializer
{
    public const DEFAULT_MONEY_PRESENT = 1000;

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

        if($player->getProfile()->organisation == Organisations::SECURITY_WORK) {
			$item = Item::get(280, 0, 1);
			$player->getInventory()->addItem($item);
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
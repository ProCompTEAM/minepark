<?php
namespace minepark\player;

use minepark\Core;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\utils\Config;

class Initializer
{
    public const DEFAULT_MONEY_PRESENT = 1000;

    public function getCore() : Core
    {
        return Core::getActive();
    }

    public function getConfig() : Config
    {
        return $this->getCore()->getPlayersConfig();
    }
    
    public function initialize(Player $player)
    {
        $config = $this->getConfig();
        $pname = strtolower($player->getName());
		
		$player->auth = false;
        $player->isnew = true;
		
		if(file_exists("players/" . $pname . ".dat")) {
            $player->isnew = false;
        }

		$player->gps = null;
		$player->bar = null;

		if(!$config->exists($pname)) {
			$this->registerPlayer($player);
		} else {
            $this->getPlayerSaves($player);
        }
        
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

        if(!$player->hasPlayedBefore() and $player->isnew) {
			$this->handleNewPlayer($player);
		}
		
		if($player->org == 4) {
			$item = Item::get(280, 0, 1);
			$player->getInventory()->addItem($item);
        }
    }
    
    public function updatePlayerSaves(Player $player)
	{
        $config = $this->getConfig();
		$pname = strtolower($player->getName());

		$config->setNested("$pname.name", $player->fullname);
		$config->setNested("$pname.org", $player->org);
		$config->setNested("$pname.education", $player->education);
		$config->setNested("$pname.temp", $player->temp);
		$config->setNested("$pname.people", $player->people);
		$config->save();
    }
    
    private function getPlayerSaves(Player $player)
	{
        $config = $this->getConfig();
		$pname = strtolower($player->getName());

		$player->fullname = $config->getNested("$pname.name");
		$player->org = $config->getNested("$pname.org");
		$player->education = $config->getNested("$pname.education");
		$player->temp = $config->getNested("$pname.temp");
		$player->people = $config->getNested("$pname.people");
    }
    
    private function registerPlayer(Player $player)
	{
        $config = $this->getConfig();
		$pname = strtolower($player->getName());

        $form = str_replace("_", " ", $player->getName());
        $config->setNested("$pname.name", $form); 
        
        $player->fullname = $form;
        
        $config->setNested("$pname.lic.car", false); 
        $config->setNested("$pname.lic.gun", false); 
        $config->setNested("$pname.lic.fishing", false);
        $config->setNested("$pname.lic.education", false);

        $config->setNested("$pname.org", 0); 
        $player->org = 0;

        $config->setNested("$pname.education", 0); 
        $player->education = 0;

        $config->setNested("$pname.temp", null); 
        $player->temp = "";

        $config->setNested("$pname.people", " "); 
        $player->people = " ";

        $config->save();
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
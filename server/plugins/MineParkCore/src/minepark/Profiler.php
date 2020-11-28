<?php
namespace minepark;

use minepark\mdc\sources\UsersSource;
use pocketmine\Player;

class Profiler
{
	public $source;
	
	public function __construct()
	{
		$this->source = $this->getCore()->getMDC()->getSource(UsersSource::ROUTE);
	}

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function isNewPlayer(Player $player) : bool
	{
		return !$this->getSource()->isUserExist($player->getName());
	}
	
    public function initializeProfile(Player $player)
	{
        if($player->isnew) {
            $player->profile = $this->getSource()->createUserInternal($player->getName());
		} else {
            $this->loadProfile($player);
        }
	}
	
	public function loadProfile(Player $player)
	{
        $player->profile = $this->getSource()->getUser($player->getName());
	}
	
	public function saveProfile(Player $player)
	{
        $this->getSource()->updateUserData($player->getProfile());
	}
	
	private function getSource() : UsersSource
	{
		return $this->source;
	}
}
?>
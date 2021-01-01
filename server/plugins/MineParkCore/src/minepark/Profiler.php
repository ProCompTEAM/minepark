<?php
namespace minepark;

use minepark\mdc\sources\UsersSource;
use minepark\player\implementations\MineParkPlayer;

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
	
	public function isNewPlayer(MineParkPlayer $player) : bool
	{
		return !$this->getSource()->isUserExist($player->getName());
	}
	
    public function initializeProfile(MineParkPlayer $player)
	{
        if($player->isnew) {
            $player->profile = $this->getSource()->createUserInternal($player->getName());
		} else {
            $this->loadProfile($player);
        }
	}
	
	public function loadProfile(MineParkPlayer $player)
	{
        $player->profile = $this->getSource()->getUser($player->getName());
	}
	
	public function saveProfile(MineParkPlayer $player)
	{
        $this->getSource()->updateUserData($player->getProfile());
	}
	
	private function getSource() : UsersSource
	{
		return $this->source;
	}
}
?>
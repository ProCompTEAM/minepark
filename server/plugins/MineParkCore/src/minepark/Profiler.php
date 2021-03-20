<?php
namespace minepark;

use minepark\providers\data\UsersSource;
use minepark\common\player\MineParkPlayer;

class Profiler
{
    private $source;
    
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
        if($player->getStatesMap()->isNew) {
            $createdUserProfile = $this->getSource()->createUserInternal($player->getName());
            $player->setProfile($createdUserProfile);
        } else {
            $this->loadProfile($player);
        }
    }
    
    public function loadProfile(MineParkPlayer $player)
    {
        $profile = $this->getSource()->getUser($player->getName());
        $player->setProfile($profile);
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
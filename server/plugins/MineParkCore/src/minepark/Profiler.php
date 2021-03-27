<?php
namespace minepark;

use minepark\common\player\MineParkPlayer;
use minepark\providers\data\UsersDataProvider;

class Profiler
{
    private $dataProvider;
    
    public function __construct()
    {
        $this->dataProvider = Providers::getUsersDataProvider();
    }

    public function getCore() : Core
    {
        return Core::getActive();
    }
    
    public function isNewPlayer(MineParkPlayer $player) : bool
    {
        return !$this->getDataProvider()->isUserExist($player->getName());
    }
    
    public function initializeProfile(MineParkPlayer $player)
    {
        if($player->getStatesMap()->isNew) {
            $createdUserProfile = $this->getDataProvider()->createUserInternal($player->getName());
            $player->setProfile($createdUserProfile);
        } else {
            $this->loadProfile($player);
        }
    }
    
    public function loadProfile(MineParkPlayer $player)
    {
        $profile = $this->getDataProvider()->getUser($player->getName());
        $player->setProfile($profile);
    }
    
    public function saveProfile(MineParkPlayer $player)
    {
        $this->getDataProvider()->updateUserData($player->getProfile());
    }
    
    private function getDataProvider() : UsersDataProvider
    {
        return $this->dataProvider;
    }
}
?>
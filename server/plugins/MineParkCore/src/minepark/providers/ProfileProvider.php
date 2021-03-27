<?php
namespace minepark\providers;

use minepark\common\player\MineParkPlayer;
use minepark\Providers;
use minepark\providers\base\Provider;
use minepark\providers\data\UsersDataProvider;

class ProfileProvider extends Provider
{
    private UsersDataProvider $usersDataProvider;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();
    }

    public function isNewPlayer(MineParkPlayer $player)
    {
        return !$this->getUsersDataProvider()->isUserExist($player->getName());
    }

    public function initializeProfile(MineParkPlayer $player)
    {
        if($player->getStatesMap()->isNew) {
            $createdUserProfile = $this->getUsersDataProvider()->createUserInternal($player->getName());
            $player->setProfile($createdUserProfile);
        } else {
            $this->loadProfile($player);
        }
    }
    
    public function loadProfile(MineParkPlayer $player)
    {
        $profile = $this->getUsersDataProvider()->getUser($player->getName());
        $player->setProfile($profile);
    }
    
    public function saveProfile(MineParkPlayer $player)
    {
        $this->getUsersDataProvider()->updateUserData($player->getProfile());
    }

    private function getUsersDataProvider()
    {
        return $this->usersDataProvider;
    }
}
?>
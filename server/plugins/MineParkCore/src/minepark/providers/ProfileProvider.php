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
        return !$this->usersDataProvider->isUserExist($player->getName());
    }

    public function initializeProfile(MineParkPlayer $player)
    {
        if($player->getStatesMap()->isNew) {
            $createdUserProfile = $this->usersDataProvider->createUserInternal($player->getName());
            $player->setProfile($createdUserProfile);
        } else {
            $this->loadProfile($player);
        }

        $this->loadSettings($player);
    }
    
    public function loadProfile(MineParkPlayer $player)
    {
        $profile = $this->usersDataProvider->getUser($player->getName());
        $player->setProfile($profile);
    }

    public function loadSettings(MineParkPlayer $player)
    {
        $settings = $this->usersDataProvider->getUserSettings($player->getName());
        $player->setSettings($settings);
    }
    
    public function saveProfile(MineParkPlayer $player)
    {
        $this->usersDataProvider->updateUserData($player->getProfile());
    }

    public function saveSettings(MineParkPlayer $player)
    {
        $this->usersDataProvider->updateUserSettings($player->getSettings());
    }
}
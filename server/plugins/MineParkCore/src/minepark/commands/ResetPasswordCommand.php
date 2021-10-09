<?php
namespace minepark\commands;

use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Providers;
use minepark\providers\data\UsersDataProvider;

class ResetPasswordCommand extends Command
{
    public const CURRENT_COMMAND = "resetpassword";

    private UsersDataProvider $usersDataProvider;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandResetPasNoName");
            return;
        }
        
        $targetPlayerName = $args[0];

        if(!$this->usersDataProvider->isUserExist($targetPlayerName)){
            $player->sendMessage("CommandResetPasNameNoExist");
            return;
        }
        
        $this->resetPassword($player, $targetPlayerName);
    }

    private function resetPassword(MineParkPlayer $sender, string $targetPlayerName)
    {
        $this->usersDataProvider->resetUserPassword($targetPlayerName);
        $sender->sendLocalizedMessage("{CommandResetPasSucces1} $targetPlayerName {CommandResetPasSucces2}");
        $targetPlayer = $this->getServer()->getPlayerByPrefix($targetPlayerName);

        if (isset($targetPlayer)) { 
            $targetPlayer->kick("CommandResetPasForPlayer");
        }
    }
}
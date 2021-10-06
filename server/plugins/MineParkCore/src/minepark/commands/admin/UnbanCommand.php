<?php

namespace minepark\commands\admin;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\administrative\BanSystem;
use minepark\defaults\Permissions;
use minepark\Providers;
use minepark\providers\data\UsersDataProvider;
use pocketmine\event\Event;

class UnbanCommand extends Command
{
    public const COMMAND_NAME = "unban";

    public const COMMAND_ALIAS = "pardon";

    private UsersDataProvider $usersDataProvider;

    private BanSystem $banSystem;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();

        $this->banSystem = Components::getComponent(BanSystem::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME,
            self::COMMAND_ALIAS
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
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $userName = $args[0];

        if(!$this->usersDataProvider->isUserExist($userName)) {
            $player->sendMessage("§eИгрока§b $userName §eне существует!");
            return;
        }

        $status = $this->banSystem->pardonUser($userName, $player->getName());

        if($status) {
            $player->sendMessage("§eИгрок§b $userName §eразблокирован!");
        } else {
            $player->sendMessage("§eИгрок§b $userName §eне является заблокированным!");
        }
    }

    private function sendInvalidCommandUsage(MineParkPlayer $player)
    {
        $player->sendMessage("§eНеверное использование данной команды. Формат: §b/unban (имя игрока)");
    }
}
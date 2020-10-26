<?php
namespace minepark\command\map;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;

class ToPointCommand extends Command
{
    public const CURRENT_COMMAND = "topoint";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::HIGH_ADMINISTRATOR,
            Permission::ADMINISTRATOR
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(!self::argumentsMin(1, $args)) {
            $player->sendMessage("§cНе указано название точки!");
            return;
        }

        $this->getCore()->getMapper()->teleportPoint($player, $args[0]);
    }
}
?>
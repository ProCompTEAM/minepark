<?php
namespace minepark\command\map;

use minepark\player\implementations\MineParkPlayer;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(!self::argumentsMin(1, $args)) {
            $player->sendMessage("PointNoArg");
            return;
        }

        $this->getCore()->getMapper()->teleportPoint($player, $args[0]);
    }
}
?>
<?php
namespace minepark\commands\map;

use minepark\common\player\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

class ToNearPointCommand extends Command
{
    public const CURRENT_COMMAND = "tonearpoint";

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
        $nearPoints = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 15);

		if(count($nearPoints) > 0)  {
            $this->getCore()->getMapper()->teleportPoint($player, $nearPoints[0]);
        } else {
            $player->sendMessage("CommandNoToNearPoint");
        }
    }
}
?>
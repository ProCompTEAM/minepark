<?php
namespace minepark\commands\map;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

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
        $nearPoints = Providers::getMapProvider()->getNearPoints($player->getPosition(), 15);

        if(count($nearPoints) > 0)  {
            Providers::getMapProvider()->teleportPoint($player, $nearPoints[0]);
        } else {
            $player->sendMessage("CommandNoToNearPoint");
        }
    }
}
?>
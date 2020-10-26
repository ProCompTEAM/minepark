<?php
namespace minepark\command\map;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;

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
            Permission::HIGH_ADMINISTRATOR,
            Permission::ADMINISTRATOR
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 15);

		if(count($nearPoints) > 0)  {
            $this->getCore()->getMapper()->teleportPoint($player, $nearPoints[0]);
        } else {
            $player->sendMessage("§cСистема не нашла точек поблизости!");
        }
    }
}
?>
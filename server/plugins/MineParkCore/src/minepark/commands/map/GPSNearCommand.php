<?php
namespace minepark\commands\map;

use minepark\defaults\Sounds;

use minepark\player\implementations\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\Command;

class GPSNearCommand extends Command
{
    public const CURRENT_COMMAND = "gpsnear";

    public const DISTANCE = 40;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), self::DISTANCE);
        $list = " §7(отсутствуют)  ";
        
		if(count($nearPoints) > 0) { 
            $list = implode(", ", $nearPoints);
        } 
        
        $player->sendLocalizedMessage("{CommandGPSNear}" . $list);
		$player->sendSound(Sounds::OPEN_NAVIGATOR);
    }
}
?>
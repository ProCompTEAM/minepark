<?php
namespace minepark\command\map;

use minepark\Sounds;

use pocketmine\Player;
use minepark\Permission;

use pocketmine\event\Event;
use minepark\command\Command;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), self::DISTANCE);
        $list = " §7(отсутствуют)  ";
        
		if(count($nearPoints) > 0) { 
            $list = implode(", ", $nearPoints);
        } 
        
        $player->sendMessage("§aМеста рядом: §e" . $list);
		$player->sendSound(Sounds::OPEN_NAVIGATOR);
    }
}
?>
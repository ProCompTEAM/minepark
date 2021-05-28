<?php
namespace minepark\commands\map;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Sounds;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\providers\MapProvider;

class GPSNearCommand extends Command
{
    public const CURRENT_COMMAND = "gpsnear";

    public const DISTANCE = 40;

    private MapProvider $mapProvider;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();
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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $nearPoints = $this->mapProvider->getNearPoints($player->getPosition(), self::DISTANCE);
        $list = " §7(отсутствуют)  ";
        
        if (count($nearPoints) > 0) {
            $list = implode(", ", $nearPoints);
        }
        
        $player->sendLocalizedMessage("{CommandGPSNear}" . $list);
        $player->sendSound(Sounds::OPEN_NAVIGATOR);
    }
}
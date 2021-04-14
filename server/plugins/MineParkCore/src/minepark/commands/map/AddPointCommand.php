<?php
namespace minepark\commands\map;

use minepark\common\player\MineParkPlayer;

use minepark\commands\base\Command;
use minepark\defaults\MapConstants;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\Providers;
use minepark\providers\MapProvider;

class AddPointCommand extends Command
{
    public const CURRENT_COMMAND = "addpoint";

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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (self::argumentsNo($args)) {
            $player->sendMessage("AddPointNoArg");
            return;
        }

        $pointName = $args[0];
        $pointType = self::argumentsCount(2, $args) ? $args[1] : MapConstants::POINT_GROUP_GENERIC;

        if (!is_numeric($pointType)) {
            $player->sendMessage("AddPointNoGroup");
            return;
        }

        $this->mapProvider->addPoint($player->getPosition(), $pointName, $pointType);
        
        $player->sendLocalizedMessage("{AddPointSuccessPart1}$pointName{AddPointSuccessPart2}");
    }
}
?>
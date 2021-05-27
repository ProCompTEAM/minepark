<?php
namespace minepark\commands\map;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\commands\base\Command;
use minepark\providers\MapProvider;
use minepark\common\player\MineParkPlayer;

class ToPointCommand extends Command
{
    public const CURRENT_COMMAND = "topoint";

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
            $player->sendMessage("PointNoArg");
            return;
        }

        $this->mapProvider->teleportPoint($player, $args[0]);
    }
}
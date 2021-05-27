<?php
namespace minepark\commands\map;

use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\providers\MapProvider;

class RemovePointCommand extends Command
{
    public const CURRENT_COMMAND = "rempoint";

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
        if(self::argumentsNo($args)) {
            $player->sendMessage("PointNoArg");
            return;
        }

        $status = $this->mapProvider->removePoint($args[0]);
        
        $player->sendMessage($status ? "CommandRemovePointSuccess" : "CommandRemovePointUnsuccess");
    }
}
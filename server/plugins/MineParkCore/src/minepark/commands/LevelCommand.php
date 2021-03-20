<?php
namespace minepark\commands;

use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class LevelCommand extends Command
{
    public const CURRENT_COMMAND = "lvl";

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
            $player->sendMessage("CommandLevelUse");
            return;
        }

        $lvl = $this->getCore()->getServer()->getLevelByName($args[0]);
        if($lvl != null) {
            $player->teleport($lvl->getSafeSpawn());
        } else {
            $player->sendMessage("CommandLevelInvalid");
        }
    }
}
?>
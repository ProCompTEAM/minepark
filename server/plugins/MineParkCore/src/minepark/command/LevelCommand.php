<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;

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
            Permission::HIGH_ADMINISTRATOR,
            Permission::ADMINISTRATOR
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
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
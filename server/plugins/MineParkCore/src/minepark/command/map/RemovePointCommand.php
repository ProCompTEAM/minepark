<?php
namespace minepark\command\map;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;

class RemovePointCommand extends Command
{
    public const CURRENT_COMMAND = "rempoint";

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
            Permission::ADMINISTRATOR_BUILDER,
            Permission::ADMINISTRATOR_TESTER
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("§cНе указано название точки!");
            return;
        }

        $status = $this->getCore()->getMapper()->removePoint($args[0]);
        
		$player->sendMessage($status ? "§eТочка была удалена!" : "§cНе удалось удалить точку!");
    }
}
?>
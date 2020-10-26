<?php
namespace minepark\command\map;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;

class AddPointCommand extends Command
{
    public const CURRENT_COMMAND = "addpoint";

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

        $param1 = $args[0];
        $param2 = self::argumentsCount(2, $args) ? $args[1] : 0;

        $this->getCore()->getMapper()->addPoint($player->getPosition(), $param1, $param2);
        
		$player->sendMessage("§3Точка§e $param1 §3добавлена в базу!");
    }
}
?>
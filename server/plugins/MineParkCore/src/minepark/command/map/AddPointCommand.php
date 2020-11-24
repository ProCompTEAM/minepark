<?php
namespace minepark\command\map;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permissions;

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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("AddPointNoArg");
            return;
        }

        $param1 = $args[0];
        $param2 = self::argumentsCount(2, $args) ? $args[1] : 0;

        $this->getCore()->getMapper()->addPoint($player->getPosition(), $param1, $param2);
        
		$player->sendMessage("AddPoint");
    }
}
?>
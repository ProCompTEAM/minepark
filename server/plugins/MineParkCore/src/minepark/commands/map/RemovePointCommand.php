<?php
namespace minepark\commands\map;

use minepark\common\player\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

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

        $status = $this->getCore()->getMapper()->removePoint($args[0]);
        
		$player->sendMessage($status ? "CommandRemovePointSuccess" : "CommandRemovePointUnsuccess");
    }
}
?>
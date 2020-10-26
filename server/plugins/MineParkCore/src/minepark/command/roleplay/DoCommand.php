<?php
namespace minepark\command\roleplay;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;
use minepark\Sounds;

class DoCommand extends Command
{
    public const CURRENT_COMMAND = "do";

    public const DISTANCE = 10;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayDoUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $this->getCore()->getChatter()->send($player, $message, "§d : ", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message, self::DISTANCE, "[DO]");
    }
}
?>
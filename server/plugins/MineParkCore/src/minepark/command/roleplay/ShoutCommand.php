<?php
namespace minepark\command\roleplay;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;
use minepark\Sounds;

class ShoutCommand extends Command
{
    public const CURRENT_COMMAND = "s";

    public const DISTANCE = 20;

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
            $player->sendMessage("CommandRolePlayShoutUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $this->getCore()->getChatter()->send($player, $message, " §3крикнул(а) §7>", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message, self::DISTANCE, "[SHOUT]");
    }
}
?>
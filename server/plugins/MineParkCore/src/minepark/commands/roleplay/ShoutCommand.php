<?php
namespace minepark\commands\roleplay;

use minepark\common\player\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\defaults\Sounds;

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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
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
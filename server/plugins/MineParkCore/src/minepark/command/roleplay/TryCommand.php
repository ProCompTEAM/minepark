<?php
namespace minepark\command\roleplay;

use minepark\player\implementations\MineParkPlayer;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permissions;
use minepark\Sounds;

class TryCommand extends Command
{
    public const CURRENT_COMMAND = "try";

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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayTryUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $actResult = mt_rand(1, 2) == 1 ? "CommandRolePlayTrySucces" : "CommandRolePlayTryUnsucces";
        
        $this->getCore()->getChatter()->send($player, $message . " " . $actResult, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message . " " . $actResult, self::DISTANCE, "[TRY]");
    }
}
?>
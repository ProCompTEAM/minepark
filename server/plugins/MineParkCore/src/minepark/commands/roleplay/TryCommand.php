<?php
namespace minepark\commands\roleplay;

use minepark\common\player\MineParkPlayer;

use minepark\commands\base\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\defaults\Sounds;

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
        
        $this->getCore()->getChatter()->sendLocalMessage($player, $message . " " . $actResult, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message . " " . $actResult, self::DISTANCE, "[TRY]");
    }
}
?>
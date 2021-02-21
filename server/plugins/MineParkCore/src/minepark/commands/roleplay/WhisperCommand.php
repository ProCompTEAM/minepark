<?php
namespace minepark\commands\roleplay;

use minepark\common\player\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\defaults\Sounds;

class WhisperCommand extends Command
{
    public const CURRENT_COMMAND = "w";

    public const DISTANCE = 4;

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
        $event->setCancelled();

        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayWhisperUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $this->getCore()->getChatter()->send($player, $message, "{CommandRolePlayWhisperDo}", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message, self::DISTANCE, "[WHISPER]");
    }
}
?>
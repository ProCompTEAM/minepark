<?php
namespace minepark\commands\roleplay;

use minepark\player\implementations\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\defaults\Sounds;

class MeCommand extends Command
{
    public const CURRENT_COMMAND = "me";

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
        $event->setCancelled();

        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayMeUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $this->getCore()->getChatter()->send($player, $message, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message, self::DISTANCE, "[ME]");
    }
}
?>
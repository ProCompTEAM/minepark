<?php
namespace minepark\command\roleplay;

use pocketmine\Player;

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

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayTryUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $actResult = mt_rand(1, 2) == 1 ? "§7[§aУдачно§7]" : "§7[§cНеудачно§7]";
        
        $this->getCore()->getChatter()->send($player, $message . " " . $actResult, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->getCore()->getTrackerModule()->actionRP($player, $message . " " . $actResult, self::DISTANCE, "[TRY]");
    }
}
?>
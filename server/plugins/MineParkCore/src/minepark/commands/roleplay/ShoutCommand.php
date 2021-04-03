<?php
namespace minepark\commands\roleplay;

use minepark\Components;

use pocketmine\event\Event;
use minepark\defaults\Sounds;

use minepark\components\GameChat;
use minepark\components\Tracking;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class ShoutCommand extends Command
{
    public const CURRENT_COMMAND = "s";

    public const DISTANCE = 20;

    private Tracking $tracking;

    private GameChat $gameChat;

    public function __construct()
    {
        $this->tracking = Components::getComponent(Tracking::class);

        $this->gameChat = Components::getComponent(GameChat::class);
    }

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
        
        $this->gameChat->sendLocalMessage($player, $message, " §3крикнул(а) §7>", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->tracking->actionRP($player, $message, self::DISTANCE, "[SHOUT]");
    }
}
?>
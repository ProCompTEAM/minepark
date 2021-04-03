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

class TryCommand extends Command
{
    public const CURRENT_COMMAND = "try";

    public const DISTANCE = 10;

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
        if (self::argumentsNo($args)) {
            $player->sendMessage("CommandRolePlayTryUse");
            return;
        }

        $message = implode(self::ARGUMENTS_SEPERATOR, $args);
        
        $actResult = mt_rand(1, 2) === 1 ? "{CommandRolePlayTrySucces}" : "{CommandRolePlayTryUnsucces}";
        
        $this->gameChat->sendLocalMessage($player, $message . " " . $actResult, "§d", self::DISTANCE);
        $player->sendSound(Sounds::ROLEPLAY);

        $this->tracking->actionRP($player, $message . " " . $actResult, self::DISTANCE, "[TRY]");
    }
}
?>
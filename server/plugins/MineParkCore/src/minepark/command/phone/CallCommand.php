<?php
namespace minepark\command\phone;

use minepark\Sounds;

use minepark\player\implementations\MineParkPlayer;
use minepark\Permissions;

use pocketmine\event\Event;
use minepark\command\Command;

class CallCommand extends Command
{
    public const CURRENT_COMMAND = "c";

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
        array_unshift($args, self::CURRENT_COMMAND);

        $this->getCore()->getPhone()->cmd($player, $args);

        $player->sendSound(Sounds::ENABLE_PHONE);
    }
}
?>
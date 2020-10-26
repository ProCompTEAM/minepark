<?php
namespace minepark\command\phone;

use minepark\Sounds;

use pocketmine\Player;
use minepark\Permission;

use pocketmine\event\Event;
use minepark\command\Command;

class SmsCommand extends Command
{
    public const CURRENT_COMMAND = "sms";

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
        array_unshift($args, self::CURRENT_COMMAND);

        $this->getCore()->getPhone()->cmd($player, $args);

        $player->sendSound(Sounds::ENABLE_PHONE);
    }
}
?>
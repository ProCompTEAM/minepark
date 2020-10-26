<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;

class AnimationCommand extends Command
{
    public const CURRENT_COMMAND = "anim";

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
        $player->sleepOn($player->getPosition()->subtract(0, 1, 0));
    }
}
?>
<?php
namespace minepark\commands;

use pocketmine\event\Event;
use pocketmine\level\Level;

use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;

class NightCommand extends Command
{
    public const CURRENT_COMMAND = "night";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ADMINISTRATOR,
            Permissions::OPERATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $player->getLevel()->setTime(Level::TIME_NIGHT);
	    $player->sendMessage("§9⌚ Вы включили §1ночь §9в игровом мире §e" . $player->getLevel()->getName());
    }
}
?>
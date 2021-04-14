<?php
namespace minepark\commands;

use pocketmine\event\Event;
use pocketmine\level\Level;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class DayCommand extends Command
{
    public const CURRENT_COMMAND = "day";

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
        $player->getLevel()->setTime(Level::TIME_DAY);
        $player->sendMessage("§9⌚ Вы включили §dдень §9в игровом мире §e" . $player->getLevel()->getName());
    }
}
?>
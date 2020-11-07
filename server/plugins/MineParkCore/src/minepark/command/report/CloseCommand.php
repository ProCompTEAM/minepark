<?php
namespace minepark\command\report;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;
use minepark\command\Command;

class CloseCommand extends Command
{
    public const CURRENT_COMMAND = "close";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::ADMINISTRATOR_MODERATOR,
			Permission::ADMINISTRATOR_HELPER
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
		if (self::argumentsNo($args)) {
			$player->sendMessage("§eТребуются аргументы.");
			return;
		}
			
		$response = $this->getCore()->getReporter()->closeReport(intval($args[0]));

		if (!$response)
		{
			$player->sendMessage("§eРепорта с таким айди не существует :(");
		}
    }
}
?>
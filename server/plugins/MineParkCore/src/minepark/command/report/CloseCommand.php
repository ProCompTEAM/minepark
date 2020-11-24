<?php
namespace minepark\command\report;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permissions;
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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
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
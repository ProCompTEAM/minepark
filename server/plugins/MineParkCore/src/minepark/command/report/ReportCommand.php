<?php
namespace minepark\command\report;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\command\Command;
use minepark\Permissions;

use minepark\utils\CallbackTask;

class ReportCommand extends Command
{
    public const CURRENT_COMMAND = "report";

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
		if (self::argumentsNo($args)) {
			$player->sendMessage("NoArguments2");
			return;
		}

		$reportMessage = implode(" ", $args);
		
		$this->getCore()->getReporter()->playerReport($player, $reportMessage);
    }
}
?>
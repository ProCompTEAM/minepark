<?php
namespace minepark\commands\report;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\commands\Command;

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

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
		if (self::argumentsNo($args)) {
			$player->sendMessage("NoArguments");
			return;
		}
			
		$response = $this->getCore()->getReporter()->closeReport(intval($args[0]));

		if (!$response)
		{
			$player->sendMessage("ReportCloseNoID");
		}
    }
}
?>
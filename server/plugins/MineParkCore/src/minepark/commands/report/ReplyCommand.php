<?php
namespace minepark\commands\report;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

use minepark\commands\base\Command;

class ReplyCommand extends Command
{
    public const CURRENT_COMMAND = "reply";

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
		if (!self::argumentsMin(2, $args)) {
            $player->sendMessage("ReportReplyNoArgs");
            return;
		}
		
		$ticketID = intval($args[0]);

		$messageArray = array_slice($args, 1);
		$messageToSend = implode(" ", $messageArray);

		$this->getCore()->getReporter()->replyReport($player, $ticketID, $messageToSend);
    }
}
?>
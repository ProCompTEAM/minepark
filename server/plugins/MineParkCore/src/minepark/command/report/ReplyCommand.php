<?php
namespace minepark\command\report;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permissions;

use minepark\command\Command;

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

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
		if (!self::argumentsMin(2, $args)) {
            $player->sendMessage("§eПравильное использование команды:§b /report <АЙДИ РЕПОРТА> <СОДЕРЖАНИЕ>");
            return;
		}
		
		$ticketID = intval($args[0]);

		$messageArray = array_slice($args, 1);
		$messageToSend = implode(" ", $messageArray);

		$this->getCore()->getReporter()->replyReport($player, $ticketID, $messageToSend);
    }
}
?>
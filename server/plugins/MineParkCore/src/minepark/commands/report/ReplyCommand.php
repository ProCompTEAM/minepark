<?php
namespace minepark\commands\report;

use minepark\Components;
use pocketmine\event\Event;

use minepark\components\Reporting;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class ReplyCommand extends Command
{
    public const CURRENT_COMMAND = "reply";

    private Reporting $reporting;

    public function __construct()
    {
        $this->reporting = Components::getComponent(Reporting::class);
    }

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
        $messageToSend = implode(self::ARGUMENTS_SEPERATOR, $messageArray);

        $this->reporting->replyReport($player, $ticketID, $messageToSend);
    }
}
?>
<?php
namespace minepark\commands\report;

use minepark\Components;
use pocketmine\event\Event;

use minepark\utils\CallbackTask;
use minepark\components\administrative\Reports;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class ReportCommand extends Command
{
    public const CURRENT_COMMAND = "report";

    private Reports $reports;

    public function __construct()
    {
        $this->reports = Components::getComponent(Reports::class);
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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (self::argumentsNo($args)) {
            $player->sendMessage("NoArguments2");
            return;
        }

        $reportMessage = implode(" ", $args);
        
        $this->reports->playerReport($player, $reportMessage);
    }
}
?>
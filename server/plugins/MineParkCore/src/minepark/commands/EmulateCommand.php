<?php
namespace minepark\commands;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\OperatorEmulating;
use minepark\defaults\Permissions;
use pocketmine\event\Event;

class EmulateCommand extends Command
{
    private const COMMAND_NAME = "emulate";

    private OperatorEmulating $operatorEmulating;

    public function __construct()
    {
        $this->operatorEmulating = Components::getComponent(OperatorEmulating::class);
    }

    public function getCommand(): array
    {
        return [
            self::COMMAND_NAME
        ];
    }

    public function getPermissions(): array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        if(!$this->canEmulate($player)) {
            $player->sendMessage("§eВы не имеете доступа к данной команде");
            return;
        }

        $player->sendForm($this->operatorEmulating->generateForm($player));
    }

    private function canEmulate(MineParkPlayer $player)
    {
        return $player->isOp() or $this->operatorEmulating->isOperator($player->getName());
    }
}
?>
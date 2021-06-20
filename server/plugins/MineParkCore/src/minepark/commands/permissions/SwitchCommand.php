<?php
namespace minepark\commands\permissions;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\administrative\PermissionsSwitch;
use minepark\defaults\Permissions;
use pocketmine\event\Event;

class SwitchCommand extends Command
{
    private const COMMAND_NAME = "q";

    private PermissionsSwitch $permissionsSwitch;

    public function __construct()
    {
        $this->permissionsSwitch = Components::getComponent(PermissionsSwitch::class);
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
        if(!$this->canSwitch($player)) {
            $player->sendMessage("§eВы не имеете доступа к данной команде");
            return;
        }

        $player->sendForm($this->permissionsSwitch->generateForm($player));
    }

    private function canSwitch(MineParkPlayer $player)
    {
        return $player->isOperator() or $this->permissionsSwitch->isOperator($player->getName());
    }
}
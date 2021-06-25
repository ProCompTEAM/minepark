<?php
namespace minepark\commands\workers;

use minepark\defaults\Sounds;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\base\Command;
use minepark\Components;
use minepark\components\organisations\Organisations;

class GetFarmCommand extends Command
{
    public const CURRENT_COMMAND = "getf";

    private Organisations $organisations;

    public function __construct()
    {
        $this->organisations = Components::getComponent(Organisations::class);
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
        $this->organisations->getFarm()->from($player);

        $player->sendSound(Sounds::ROLEPLAY);
    }
}
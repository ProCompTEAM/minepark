<?php
namespace minepark\commands\workers;

use minepark\defaults\Sounds;

use minepark\player\implementations\MineParkPlayer;
use minepark\defaults\Permissions;

use pocketmine\event\Event;
use minepark\commands\Command;

class GetFarmCommand extends Command
{
    public const CURRENT_COMMAND = "getf";

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
        $this->getCore()->getOrganisationsModule()->farm->from($player);

		$player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
<?php
namespace minepark\command\workers;

use minepark\Sounds;

use minepark\player\implementations\MineParkPlayer;
use minepark\Permissions;

use pocketmine\event\Event;
use minepark\command\Command;

class PutFarmCommand extends Command
{
    public const CURRENT_COMMAND = "putf";

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
        $this->getCore()->getOrganisationsModule()->farm->to($player);

		$player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
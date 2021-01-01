<?php
namespace minepark\command\workers;

use minepark\Sounds;

use minepark\player\implementations\MineParkPlayer;
use minepark\Permissions;

use pocketmine\event\Event;
use minepark\command\Command;

class PutBoxCommand extends Command
{
    public const CURRENT_COMMAND = "putbox";

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
        $this->getCore()->getOrganisationsModule()->workers->putbox($player);

		$player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
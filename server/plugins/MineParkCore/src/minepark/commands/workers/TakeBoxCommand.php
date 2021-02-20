<?php
namespace minepark\commands\workers;

use minepark\player\implementations\MineParkPlayer;

use minepark\commands\Command;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\defaults\Sounds;

class TakeBoxCommand extends Command
{
    public const CURRENT_COMMAND = "takebox";

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
        $this->getCore()->getOrganisationsModule()->workers->takebox($player);
        
        $player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
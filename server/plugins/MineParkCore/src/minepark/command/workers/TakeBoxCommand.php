<?php
namespace minepark\command\workers;

use pocketmine\Player;

use minepark\command\Command;
use pocketmine\event\Event;

use minepark\Permission;
use minepark\Sounds;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $this->getCore()->getOrganisationsModule()->workers->takebox($player);
        
        $player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
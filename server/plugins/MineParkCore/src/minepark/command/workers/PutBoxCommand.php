<?php
namespace minepark\command\workers;

use minepark\Sounds;

use pocketmine\Player;
use minepark\Permission;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $this->getCore()->getOrganisationsModule()->workers->putbox($player);

		$player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
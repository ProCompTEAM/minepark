<?php
namespace minepark\command\workers;

use minepark\Sounds;

use pocketmine\Player;
use minepark\Permission;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $this->getCore()->getOrganisationsModule()->farm->to($player);

		$player->sendSound(Sounds::ROLEPLAY);
    }
}
?>
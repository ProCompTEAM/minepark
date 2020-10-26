<?php
namespace minepark\modules\organizations\command;

use minepark\modules\organizations\Organizations;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\event\Event;

class ShowCommand extends OrganizationsCommand
{
    public const CURRENT_COMMAND = "show";

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
        $organModule = $this->getCore()->getOrganisationsModule();

        $oid = $player->org;

        if($oid == Organizations::NO_WORK) {
            $player->sendMessage("§cУ вас нет удостоверения!");
            return;
        }

		$this->getCore()->getChatter()->send($player, "§8(§dВ руках удостоверение *".$organModule->getName($oid, false)."*§8)", "§d : ", 10);
    }
}
?>
<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permissions;

use pocketmine\Player;
use pocketmine\event\Event;

class ShowCommand extends OrganisationsCommand
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
            Permissions::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $organModule = $this->getCore()->getOrganisationsModule();

        $oid = $player->getProfile()->organisation;

        if($oid == Organisations::NO_WORK) {
            $player->sendMessage("CommandShowNoWork");
            return;
        }

		$this->getCore()->getChatter()->send($player, "{CommandShowHandLic}".$organModule->getName($oid, false)."*§8)", "§d : ", 10);
    }
}
?>
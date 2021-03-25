<?php
namespace minepark\commands\organisations;

use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;
use minepark\commands\organisations\base\OrganisationsCommand;

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

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $organModule = $this->getCore()->getOrganisationsModule();

        $oid = $player->getProfile()->organisation;

        if($oid == Organisations::NO_WORK) {
            $player->sendMessage("CommandShowNoWork");
            return;
        }

        $this->getCore()->getChatter()->sendLocalMessage($player, "{CommandShowHandLic}".$organModule->getName($oid, false)."*§8)", "§d : ", 10);
    }
}
?>
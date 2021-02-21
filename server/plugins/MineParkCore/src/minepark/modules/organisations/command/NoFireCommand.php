<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

class NoFireCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "nofire";

    public const CURRENT_COMMAND_ALIAS1 = "clean";
    public const CURRENT_COMMAND_ALIAS2 = "clear";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS1,
            self::CURRENT_COMMAND_ALIAS2
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

        if($oid != Organisations::EMERGENCY_WORK) {
            $player->sendMessage("§cВы не являетесь работником службы спасения!");
            return;
        }

        $organModule->getNoFire()->clean($player);
    }
}
?>
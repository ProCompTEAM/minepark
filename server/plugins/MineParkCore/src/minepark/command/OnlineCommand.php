<?php
namespace minepark\command;

use minepark\player\implementations\MineParkPlayer;
use pocketmine\event\Event;

use minepark\Permissions;

class OnlineCommand extends Command
{
    public const CURRENT_COMMAND = "online";

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
        $admins = $this->getCore()->getApi()->getAdministration(true);

        if(count($admins) < 1) {
            $player->sendMessage("CommandOnlineNoAdmins");
            return;
        }

        $player->sendMessage("CommandOnlineMessage");
        $player->sendMessage(implode("\n - ", $admins));
    }
}
?>
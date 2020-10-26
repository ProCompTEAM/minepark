<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
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
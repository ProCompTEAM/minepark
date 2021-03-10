<?php
namespace minepark\commands\organisations;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

class RadioCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "r";

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
        if (self::argumentsNo($args)) {
            return $player->sendMessage("§eПравильное использование этой команды: /o r [ТЕКСТ]");
        }

        $oid = $player->getProfile()->organisation;

        $message = implode(" ", $args);

        if($oid >= 1) {
            foreach($this->core->getServer()->getOnlinePlayers() as $p) {
                if($p->getProfile()->organisation == $oid) {
                    $p->sendMessage("§d[РАЦИЯ] §7".$player->getProfile()->fullName." §4> §7".$message);
                }
            }
            return;
        }

        $player->sendMessage("§6У вас нет рации!");
    }
}
?>
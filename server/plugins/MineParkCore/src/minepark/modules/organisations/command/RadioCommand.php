<?php
namespace minepark\modules\organisations\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if (self::argumentsNo($args)) {
            $player->sendMessage("§eПравильное использование этой команды: /r [ТЕКСТ]");
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
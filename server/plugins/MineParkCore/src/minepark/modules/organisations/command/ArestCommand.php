<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permission;
use minepark\Api;

use pocketmine\Player;
use pocketmine\event\Event;

class ArestCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "arest";

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
        if (!$this->canArrest($player)) {
            $player->sendMessage("§cКоманда доступна только сотрудникам правоохранительных органов и юристам!");
            return;
        }

        $this->getCore()->getChatter()->send($player, "§8(§dв руках наручники§8)", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("§6Подойдите к правонарушителю ближе!");
            return;
        }

        foreach($plrs as $plr) {
            $this->arrestPlayer($plr, $player);
        }
    }

    private function canArrest(Player $p) : bool
    {
        return $p->getProfile()->organisation == Organisations::GOVERNMENT_WORK or $p->getProfile()->organisation == Organisations::SECURITY_WORK;
    }

    private function getPlayersNear(Player $p) : array
    {
        $x = $p->getX();
        $y = $p->getY(); 
        $z = $p->getZ();

        $allplayers = $this->getCore()->getApi()->getRegionPlayers($x, $y, $z, 5);

        $players = array();

        foreach ($allplayers as $currp) {
            if ($currp->getName() != $p->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }

    private function arrestPlayer(Player $playerToArrest, Player $arrester)
    {
        if(!$this->getCore()->getApi()->existsAttr($playerToArrest, Api::ATTRIBUTE_WANTED)) {
            $arrester->sendMessage("§9Для ареста вам необходимо оглушить §3".$playerToArrest->getProfile()->fullName);
            $arrester->sendMessage("§7Чтобы оглушить кого-то, ударьте его дубинкой §8((палкой))§7!");
            return;
        }

        $this->getCore()->getApi()->arest($playerToArrest);

        $playerToArrest->sendMessage("§9Вас арестовал §3".$arrester->getProfile()->fullName);
        $arrester->sendMessage("§9Вы арестовали §3".$playerToArrest->getProfile()->fullName);
    }
}
?>
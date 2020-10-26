<?php
namespace minepark\modules\organizations\command;

use minepark\modules\organizations\Organizations;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\event\Event;

class ChangeNameCommand extends OrganizationsCommand
{
    public const CURRENT_COMMAND = "changename";

    public const POINT_NAME = "Мэрия";

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
        if (!$this->canGiveDocuments($player)) {
            $player->sendMessage("§cВы не документовед!");
            return;
        }

        if (!$this->isNearPoint($player)) {
            $player->sendMessage("§6Рядом нет мэрии! (/gps)");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("§6Рядом с вами нет клиентов!");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $this->moveThemOut($plrs, $player);
            return;
        }

        if (!self::argumentsMin(2, $args)) {
            $player->sendMessage("§cФормат: /o changename <имя> <псевдоним>");
            return;
        }

        $this->tryChangeName($plrs[0], $player, $args[0], $args[1]);
    }

    private function tryChangeName(Player $toPlayer, Player $government, string $name, string $surname)
    {
        
        $oldname = $toPlayer->fullname;
        $toPlayer->fullname = $name . ' ' . $surname;

        $this->getCore()->getInitializer()->updatePlayerSaves($toPlayer);
        $toPlayer->setTip("§aпоздравляем!","§9$oldname §7>>> §e".$toPlayer->fullname, 5);

        $this->getCore()->getBank()->givePlayerMoney($government, 10);
        $government->sendMessage("§bВы изменили имя клиента с §9$oldname §7на §e".$toPlayer->fullname);
    }

    private function moveThemOut(array $plrs, Player $government)
    {
        $this->getCore()->getChatter()->send($government, "Граждане, не мешайте проведению процесса!");
        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("§6Вы мешаете проведению операции, отойдите дальше!");
            }
        }
        $government->sendMessage("§6Операция требует приватности, поэтому не была произведена!");
    }

    private function canGiveDocuments(Player $p) : bool
    {
        return $p->org == Organizations::GOVERNMENT_WORK or $p->org == Organizations::LAWYER_WORK;
    }

    private function isNearPoint(Player $p) : bool
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($p->getPosition(), 32);
		return in_array(self::POINT_NAME, $plist);
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
}
?>
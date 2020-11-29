<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permissions;

use pocketmine\Player;
use pocketmine\event\Event;

class ChangeNameCommand extends OrganisationsCommand
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
            Permissions::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if (!$this->canGiveDocuments($player)) {
            $player->sendMessage("CommandChangeNameNoCanGive");
            return;
        }

        if (!$this->isNearPoint($player)) {
            $player->sendMessage("CommandChangeNameNoGov");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandChangeNameNoPlayer");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $this->moveThemOut($plrs, $player);
            return;
        }

        if (!self::argumentsMin(2, $args)) {
            $player->sendMessage("CommandChangeNameHelp");
            return;
        }

        $this->tryChangeName($plrs[0], $player, $args[0], $args[1]);
    }

    private function tryChangeName(Player $toPlayer, Player $government, string $name, string $surname)
    {
        
        $oldname = $toPlayer->getProfile()->fullName;
        $toPlayer->getProfile()->fullName = $name . ' ' . $surname;

        $this->getCore()->getProfiler()->saveProfile($toPlayer);
        $toPlayer->setTip("§aпоздравляем!","§9$oldname §7>>> §e".$toPlayer->getProfile()->fullName, 5);

        $this->getCore()->getBank()->givePlayerMoney($government, 10);
        $government->sendLocalizedMessage("{CommandChangeName}".$toPlayer->getProfile()->fullName);
    }

    private function moveThemOut(array $plrs, Player $government)
    {
        $this->getCore()->getChatter()->send($government, "{CommandChangeNameManyPlayers1}");
        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandChangeNameManyPlayers2");
            }
        }
        $government->sendMessage("CommandChangeNameManyPlayers3");
    }

    private function canGiveDocuments(Player $p) : bool
    {
        return $p->getProfile()->organisation == Organisations::GOVERNMENT_WORK or $p->getProfile()->organisation == Organisations::LAWYER_WORK;
    }

    private function isNearPoint(Player $p) : bool
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($p->getPosition(), 32);
		return in_array(self::POINT_NAME, $plist);
    }

    private function getPlayersNear(Player $player) : array
    {
        $allplayers = $this->getCore()->getApi()->getRegionPlayers($player, 5);

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
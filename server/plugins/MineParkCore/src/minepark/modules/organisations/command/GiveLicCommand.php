<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permissions;

use pocketmine\Player;
use pocketmine\event\Event;

class GiveLicCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "givelic";

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
        $organModule = $this->getCore()->getOrganisationsModule();

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

        $this->tryGiveLicense($plrs[0], $player);
    }

    private function tryGiveLicense(Player $toPlayer, Player $government)
    {
        $this->getCore()->getChatter()->send($government, "§8(§dв руках ключи от сейфа§8)", "§d : ", 10);

        $government->sendMessage("§7В данный момент лицензий нет!");
        $toPlayer->sendMessage("§bВ данный момент отсутсвуют лицензии в наличии.");
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
            if ($currp->getName() != $player->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }
}
?>
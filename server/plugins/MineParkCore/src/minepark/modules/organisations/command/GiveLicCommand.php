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
            $player->sendMessage("CommandGiveLicNoCanGive");
            return;
        }

        if (!$this->isNearPoint($player)) {
            $player->sendMessage("CommandGiveLicNoGov");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandGiveLicNoPlayer");
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
        $this->getCore()->getChatter()->send($government, "{CommandGiveLicKeys}", "§d : ", 10);

        $government->sendMessage("CommandGiveLicNoLic1");
        $toPlayer->sendMessage("CommandGiveLicNoLic2");
    }

    private function moveThemOut(array $plrs, Player $government)
    {
        $this->getCore()->getChatter()->send($government, "{CommandGiveLicManyPlayers1}");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandGiveLicManyPlayers2");
            }
        }

        $government->sendMessage("CommandGiveLicManyPlayers3");
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
<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\GameChat;
use minepark\components\organisations\Organisations;
use minepark\providers\MapProvider;

class GiveLicCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "givelic";

    public const POINT_NAME = "Мэрия";

    private MapProvider $mapProvider;

    private GameChat $gameChat;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->gameChat = Components::getComponent(GameChat::class);
    }

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

    private function tryGiveLicense(MineParkPlayer $toPlayer, MineParkPlayer $government)
    {
        $this->gameChat->sendLocalMessage($government, "{CommandGiveLicKeys}", "§d : ", 10);

        $government->sendMessage("CommandGiveLicNoLic1");
        $toPlayer->sendMessage("CommandGiveLicNoLic2");
    }

    private function moveThemOut(array $plrs, MineParkPlayer $government)
    {
        $this->gameChat->sendLocalMessage($government, "{CommandGiveLicManyPlayers1}");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandGiveLicManyPlayers2");
            }
        }

        $government->sendMessage("CommandGiveLicManyPlayers3");
    }

    private function canGiveDocuments(MineParkPlayer $player) : bool
    {
        return $player->getProfile()->organisation === Organisations::GOVERNMENT_WORK or $player->getProfile()->organisation === Organisations::LAWYER_WORK;
    }

    private function isNearPoint(MineParkPlayer $player) : bool
    {
        $plist = $this->mapProvider->getNearPoints($player->getPosition(), 32);
        return in_array(self::POINT_NAME, $plist);
    }

    private function getPlayersNear(MineParkPlayer $player) : array
    {
        $allplayers = $this->getCore()->getRegionPlayers($player, 5);

        $players = array();
        foreach ($allplayers as $currp) {
            if ($currp->getName() != $player->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }
}
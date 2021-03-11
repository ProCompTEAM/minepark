<?php
namespace minepark\commands\organisations;

use minepark\Api;
use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;
use minepark\commands\organisations\base\OrganisationsCommand;

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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (!$this->canArrest($player)) {
            $player->sendMessage("CommandArestCan");
            return;
        }

        $this->getCore()->getChatter()->send($player, "{CommandArestCuff}", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandArestNoPlayers");
            return;
        }

        foreach($plrs as $plr) {
            $this->arrestPlayer($plr, $player);
        }
    }

    private function canArrest(MineParkPlayer $player) : bool
    {
        return $p->getProfile()->organisation == Organisations::GOVERNMENT_WORK or $player->getProfile()->organisation == Organisations::SECURITY_WORK;
    }

    private function getPlayersNear(MineParkPlayer $player) : array
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

    private function arrestPlayer(MineParkPlayer $playerToArrest, MineParkPlayer $arrester)
    {
        if(!$this->getCore()->getApi()->existsAttr($playerToArrest, Api::ATTRIBUTE_WANTED)) {
            $arrester->sendLocalizedMessage("{CommandArestDoStun1}".$playerToArrest->getProfile()->fullName);
            $arrester->sendMessage("CommandArestDoStun2");
            return;
        }

        $this->getCore()->getApi()->arest($playerToArrest);

        $playerToArrest->sendLocalizedMessage("{CommandArestPrisoner}".$arrester->getProfile()->fullName);
        $arrester->sendLocalizedMessage("{CommandArestPolice}".$playerToArrest->getProfile()->fullName);
    }
}
?>
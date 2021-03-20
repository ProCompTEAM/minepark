<?php
namespace minepark\commands\organisations;

use minepark\Api;
use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;
use minepark\commands\organisations\base\OrganisationsCommand;

class InfoCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "info";

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
        if (!$this->canGetInfo($player)) {
            $player->sendMessage("CommandInfoNoCan");
            return;
        }

        $this->getCore()->getChatter()->send($player, "{CommandInfoPrint}", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandInfoNoPlayer");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $player->sendMessage("CommandInfoManyPlayer");
        }

        $this->getPlayerInfo($plrs[0], $player);
    }

    private function canGetInfo(MineParkPlayer $p) : bool
    {
        return $p->getProfile()->organisation == Organisations::GOVERNMENT_WORK or $p->getProfile()->organisation == Organisations::SECURITY_WORK;
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

    private function getPlayerInfo(MineParkPlayer $playerToInfo, MineParkPlayer $requestor)
    {
        $f = "§bДоп.информация о человеке " . $playerToInfo->getProfile()->fullName . ":";

        $f .= "\n§a > §eДокументы: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_HAS_PASSPORT) ? "§aда" : "§cнет");
        $f .= "\n§a > §eАрестован: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_ARRESTED) ? "§aда" : "§cнет");
        $f .= "\n§a > §eВ розыске: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_WANTED) ? "§aда" : "§cнет");

        $requestor->sendMessage($f);
    }
}
?>
<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permissions;
use minepark\Api;

use pocketmine\Player;
use pocketmine\event\Event;

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

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if (!$this->canGetInfo($player)) {
            $player->sendMessage("§cКоманда доступна только сотрудникам правоохранительных органов и юристам!");
            return;
        }

        $this->getCore()->getChatter()->send($player, "§8(§dдостал таблицу отпечатков пальцев§8)", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("§6Подойдите к гражданину поближе!");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $player->sendMessage("§6Рядом слишком много людей!");
        }

        $this->getPlayerInfo($plrs[0], $player);
    }

    private function canGetInfo(Player $p) : bool
    {
        return $p->getProfile()->organisation == Organisations::GOVERNMENT_WORK or $p->getProfile()->organisation == Organisations::SECURITY_WORK;
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

    private function getPlayerInfo(Player $playerToInfo, Player $requestor)
    {
        $f = "§bДоп.информация о человеке ".$playerToInfo->getProfile()->fullName.":";

        $f .= "\n§a > §eДокументы: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_HAS_PASSPORT) ? "§aда" : "§cнет");
        $f .= "\n§a > §eАрестован: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_ARRESTED) ? "§aда" : "§cнет");
        $f .= "\n§a > §eВ розыске: ". ($this->core->getApi()->existsAttr($playerToInfo, Api::ATTRIBUTE_WANTED) ? "§aда" : "§cнет");

        $requestor->sendMessage($f);
    }
}
?>
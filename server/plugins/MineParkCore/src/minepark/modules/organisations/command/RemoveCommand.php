<?php
namespace minepark\modules\organisations\command;

use minepark\Permission;
use minepark\Api;

use pocketmine\Player;
use pocketmine\event\Event;

class RemoveCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "remove";
    public const CURRENT_COMMAND_ALIAS = "reject";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS
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
        $organModule = $this->getCore()->getOrganisationsModule();

        if (!$this->isBoss($player)) {
            $player->sendMessage("§6Эту команду могут использовать только главы фракций!");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("§6Подойдите к гражданину поближе!");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $player->sendMessage("§6Рядом слишком много людей!");
        }

        $this->tryRejectGuy($plrs[0], $player);
    }

    private function tryRejectGuy(Player $player, Player $boss)
    {
        if ($player->getProfile()->organisation != $boss->getProfile()->organisation) {
            $boss->sendMessage("§6Вы не можете уволить данного гражданина, так как он не находится в вашей организации!");
        }

        $player->getProfile()->organisation = 0;
        $this->core->getInitializer()->updatePlayerSaves($player);

        $boss->sendMessage("Вы уволили гражданина " . $player->getProfile()->fullName);
        $player->sendMessage("Вас уволил с работы начальник ". $boss->getProfile()->fullName ."!");
    }

    private function isBoss(Player $p) : bool
    {
        return $this->getCore()->getApi()->existsAttr($p, Api::ATTRIBUTE_BOSS);
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
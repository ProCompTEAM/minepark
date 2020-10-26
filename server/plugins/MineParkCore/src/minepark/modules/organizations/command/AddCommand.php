<?php
namespace minepark\modules\organizations\command;

use minepark\Permission;
use minepark\Api;

use pocketmine\Player;
use pocketmine\event\Event;

class AddCommand extends OrganizationsCommand
{
    public const CURRENT_COMMAND = "add";
    public const CURRENT_COMMAND_ALIAS = "join";

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

        $this->tryChangeOrganization($plrs[0], $player);
    }

    private function tryChangeOrganization(Player $player, Player $boss)
    {
        $player->org = $boss->org;
        $this->getCore()->getInitializer()->updatePlayerSaves($player);

		$boss->sendMessage("Вы приняли на работу гражданина " . $player->fullname);
		$player->sendMessage("Теперь вы ".$this->core->getOrganisationsModule()->getName($player->org));
    }

    private function isBoss(Player $p) : bool
    {
        return $this->getCore()->getApi()->existsAttr($p, Api::ATTRIBUTE_BOSS);
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
<?php
namespace minepark\components\organisations\command;

use minepark\defaults\Permissions;
use minepark\Api;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

class AddCommand extends OrganisationsCommand
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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if (!$this->isBoss($player)) {
            $player->sendMessage("CommandAddNoBoss");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandAddNoPlayers");
            return;
        }

        if (self::argumentsMin(2, $plrs)) {
            $player->sendMessage("CommandAddManyPlayers");
        }

        $this->tryChangeOrganisation($plrs[0], $player);
    }

    private function tryChangeOrganisation(MineParkPlayer $player, MineParkPlayer $boss)
    {
        $player->getProfile()->organisation = $boss->getProfile()->organisation;
        $this->getCore()->getProfiler()->saveProfile($player);

		$boss->sendLocalizedMessage("{CommandAdd}" . $player->getProfile()->fullName);
		$player->sendLocalizedMessage("{GroupYou}".$this->core->getOrganisationsModule()->getName($player->getProfile()->organisation));
    }

    private function isBoss(MineParkPlayer $player) : bool
    {
        return $this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS);
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
}
?>
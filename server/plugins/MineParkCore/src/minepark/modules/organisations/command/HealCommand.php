<?php
namespace minepark\modules\organisations\command;

use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;
use minepark\modules\organisations\Organisations;

class HealCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "heal";

    public const POINT_NAME = "Больница";

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
        if (!$this->isHealer($player)) {
            $player->sendMessage("CommandHealNoCanHeal");
            return;
        }

		if (!$this->isNearPoint($player)) {
            $player->sendMessage("CommandHealNoHospital");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if ($plrs == 1) {
            $this->healPlayer($player, $plrs[0]);
        } elseif ($plrs > 1) {
            $this->moveThemOut($plrs, $player);
        } else {
            $player->sendMessage("CommandHealNoPlayers");
        }
    }

    private function isHealer(MineParkPlayer $plr)
    {
        return $plr->getProfile()->organisation == Organisations::DOCTOR_WORK;
    }

    private function isNearPoint(MineParkPlayer $player) : bool
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 32);

		return in_array(self::POINT_NAME, $plist);
    }

    private function moveThemOut(array $plrs, MineParkPlayer $healer)
    {
        $this->getCore()->getChatter()->send($healer, "{CommandHealManyPlayers1}");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandHealManyPlayers2");
            }
        }

        $healer->sendMessage("CommandHealManyPlayers3");
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

    private function healPlayer(MineParkPlayer $healer, MineParkPlayer $playerToHeal)
    {
        $playerToHeal->removeAllEffects();
        $playerToHeal->setHealth($playerToHeal->getMaxHealth());

		$this->getCore()->getChatter()->send($healer, "{CommandHealDo}");
		Providers::getBankingProvider()->givePlayerMoney($healer, 500);
    }
}
?>
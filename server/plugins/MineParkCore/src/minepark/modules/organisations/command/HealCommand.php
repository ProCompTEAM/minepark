<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\event\Event;

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
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if (!$this->isHealer($player)) {
            $player->sendMessage("§cВы не сотрудник больницы!");
            return;
        }

		if (!$this->isNearPoint($player)) {
            $player->sendMessage("§6Рядом нет больницы! (/gps)");
            return;
        }

        $plrs = $this->getPlayersNear($player);

        if ($plrs == 1) {
            $this->healPlayer($player, $plrs[0]);
        } elseif ($plrs > 1) {
            $this->moveThemOut($plrs, $player);
        } else {
            $player->sendMessage("§6Рядом с вами нет пациентов!");
        }
    }

    private function isHealer(Player $plr)
    {
        return $plr->getProfile()->organisation == Organisations::DOCTOR_WORK;
    }

    private function isNearPoint(Player $p) : bool
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($p->getPosition(), 32);

		return in_array(self::POINT_NAME, $plist);
    }

    private function moveThemOut(array $plrs, Player $healer)
    {
        $this->getCore()->getChatter()->send($healer, "Больные, вас слишком много! В очередь!");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("§6Вы мешаете проведению операции, отойдите дальше!");
            }
        }

        $healer->sendMessage("§6Операция требует приватности, поэтому не была произведена!");
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

    private function healPlayer(Player $healer, Player $playerToHeal)
    {
        $playerToHeal->removeAllEffects();
        $playerToHeal->setHealth($playerToHeal->getMaxHealth());

		$this->getCore()->getChatter()->send($healer, "Теперь Вы снова здоровы! Можете идти.");
		$this->getCore()->getBank()->givePlayerMoney($healer, 500);
    }
}
?>
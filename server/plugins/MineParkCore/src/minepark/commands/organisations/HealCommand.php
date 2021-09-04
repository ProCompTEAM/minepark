<?php
namespace minepark\commands\organisations;

use minepark\Providers;
use minepark\Components;
use pocketmine\event\Event;

use minepark\components\chat\Chat;
use minepark\defaults\Permissions;
use minepark\providers\MapProvider;
use minepark\providers\BankingProvider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\OrganisationConstants;
use minepark\commands\base\OrganisationsCommand;
use minepark\components\organisations\Organisations;

class HealCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "heal";

    public const POINT_NAME = "Больница";

    private MapProvider $mapProvider;

    private BankingProvider $bankingProvider;

    private Chat $chat;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->bankingProvider = Providers::getBankingProvider();

        $this->chat = Components::getComponent(Chat::class);
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
        return $plr->getSettings()->organisation === OrganisationConstants::DOCTOR_WORK;
    }

    private function isNearPoint(MineParkPlayer $player) : bool
    {
        $plist = $this->mapProvider->getNearPoints($player->getPosition(), 32);

        return in_array(self::POINT_NAME, $plist);
    }

    private function moveThemOut(array $plrs, MineParkPlayer $healer)
    {
        $this->chat->sendLocalMessage($healer, "{CommandHealManyPlayers1}");

        foreach($plrs as $id => $p) {
            if($id > 1) {
                $p->sendMessage("CommandHealManyPlayers2");
            }
        }

        $healer->sendMessage("CommandHealManyPlayers3");
    }

    private function getPlayersNear(MineParkPlayer $player) : array
    {
        $allplayers = $this->getCore()->getRegionPlayers($player->getPosition(), 5);

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
        $playerToHeal->getEffects()->clear();
        $playerToHeal->setHealth($playerToHeal->getMaxHealth());

        $this->g->sendLocalMessage($healer, "{CommandHealDo}");
        $this->bankingProvider->givePlayerMoney($healer, 500);
    }
}
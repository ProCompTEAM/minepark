<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\GameChat;
use minepark\components\organisations\Organisations;

class ArestCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "arest";

    private GameChat $gameChat;

    public function __construct()
    {
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
        if (!$this->canArrest($player)) {
            $player->sendMessage("CommandArestCan");
            return;
        }

        $this->gameChat->sendLocalMessage($player, "{CommandArestCuff}", "§d : ", 10);

        $plrs = $this->getPlayersNear($player);

        if (self::argumentsNo($plrs)) {
            $player->sendMessage("CommandArestNoPlayers");
            return;
        }

        foreach ($plrs as $plr) {
            $this->arrestPlayer($plr, $player);
        }
    }

    private function canArrest(MineParkPlayer $player) : bool
    {
        return $player->getProfile()->organisation === Organisations::GOVERNMENT_WORK or $player->getProfile()->organisation === Organisations::SECURITY_WORK;
    }

    private function getPlayersNear(MineParkPlayer $player) : array
    {
        $allplayers = $this->getCore()->getRegionPlayers($player, 5);

        $players = array();

        foreach ($allplayers as $currp) {
            if ($currp->getName() !== $player->getName()) {
                $players[] = $currp;
            }
        }

        return $players;
    }

    private function arrestPlayer(MineParkPlayer $playerToArrest, MineParkPlayer $arrester)
    {
        $playerToArrest->arest();
        $playerToArrest->setImmobile(false);

        $playerToArrest->sendLocalizedMessage("{CommandArestPrisoner}".$arrester->getProfile()->fullName);
        $arrester->sendLocalizedMessage("{CommandArestPolice}".$playerToArrest->getProfile()->fullName);
    }
}
?>
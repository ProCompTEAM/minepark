<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use minepark\Providers;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;
use minepark\providers\ProfileProvider;

class AddCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "add";
    public const CURRENT_COMMAND_ALIAS = "join";

    private ProfileProvider $profileProvider;

    public function __construct()
    {
        $this->profileProvider = Providers::getProfileProvider();
    }

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
        $player->getSettings()->organisation = $boss->getSettings()->organisation;
        $this->profileProvider->saveProfile($player);

        $boss->sendLocalizedMessage("{CommandAdd}" . $player->getProfile()->fullName);
        $player->sendLocalizedMessage("{GroupYou}".$this->core->getOrganisationsModule()->getName($player->getSettings()->organisation));
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
}
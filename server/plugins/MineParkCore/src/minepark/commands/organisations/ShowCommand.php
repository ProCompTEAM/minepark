<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use pocketmine\event\Event;
use minepark\defaults\Permissions;

use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\GameChat;
use minepark\components\organisations\Organisations;

class ShowCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "show";

    private Organisations $organisations;
    
    private GameChat $gameChat;

    public function __construct()
    {
        $this->organisations = Components::getComponent(Organisations::class);

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
        $organisationId = $player->getProfile()->organisation;

        if ($organisationId === Organisations::NO_WORK) {
            $player->sendMessage("CommandShowNoWork");
            return;
        }

        $organisationName = $this->organisations->getName($organisationId, false);

        $this->gameChat->sendLocalMessage($player, "{CommandShowHandLic}" . $organisationName . "*§8)", "§d : ", 10);
    }
}
?>
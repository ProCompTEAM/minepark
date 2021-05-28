<?php
namespace minepark\commands\map;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\map\ATM;
use minepark\defaults\MapConstants;
use minepark\defaults\Permissions;
use minepark\Providers;
use minepark\providers\MapProvider;
use pocketmine\event\Event;

class ATMCommand extends Command
{
    private const COMMAND_NAME = "atm";

    private const ATM_DISTANCE = 4;

    private MapProvider $mapProvider;

    private ATM $atm;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();

        $this->atm = Components::getComponent(ATM::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        if(!$this->isNearATM($player)) {
            $player->sendMessage("§eПоблизости нету банкомата!");
            return;
        }

        $this->atm->initializeMenu($player);
    }

    private function isNearATM(MineParkPlayer $player) : bool
    {
        $points = $this->mapProvider->getNearPoints($player->asPosition(), self::ATM_DISTANCE, false);

        foreach($points as $point) {
            if($point->groupId === MapConstants::POINT_GROUP_ATM) {
                return true;
            }
        }

        return false;
    }
}
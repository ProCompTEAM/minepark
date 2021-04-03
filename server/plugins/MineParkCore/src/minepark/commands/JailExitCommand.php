<?php
namespace minepark\commands;

use minepark\Api;

use minepark\Providers;
use minepark\Components;
use pocketmine\event\Event;
use pocketmine\level\Position;
use minepark\components\GameChat;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\defaults\MapConstants;
use minepark\providers\BankingProvider;
use minepark\common\player\MineParkPlayer;
use minepark\providers\MapProvider;

class JailExitCommand extends Command
{
    public const CURRENT_COMMAND = "jexit";

    public const FREE_PRICE = 50000;

    public const DOOR_DISTANCE = 20;

    private BankingProvider $bankingProvider;

    private MapProvider $mapProvider;

    private GameChat $gameChat;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->mapProvider = Providers::getMapProvider();

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
        if($this->getJailPoint($player->getPosition()) == null or !$this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_ARRESTED)) {
            $player->sendMessage("CommandJailExitNoPoint");
            return;
        }
        
        if($this->bankingProvider->takePlayerMoney($player, self::FREE_PRICE)) {
            $this->gameChat->sendLocalMessage($player, "{CommandJailExit}", "§d", self::DOOR_DISTANCE);

            $this->getCore()->getApi()->changeAttr($player, "A", false);
            $this->getCore()->getApi()->changeAttr($player, "W", false);

            $this->mapProvider->teleportPoint($player, MapConstants::POINT_NAME_ADIMINISTRATION);

            $player->getStatesMap()->bar = null;
        } else {
            $player->sendLocalizedMessage("{CommandJailExitNoMoney}". self::FREE_PRICE);
        }
    }

    private function getJailPoint(Position $position) : ?string
    {
        $plist = $this->mapProvider->getNearPoints($position, self::DOOR_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == MapConstants::POINT_NAME_JAIL) {
                return $point;
            }
        }

        return null;
    }
}
?>
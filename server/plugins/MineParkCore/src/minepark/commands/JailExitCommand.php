<?php
namespace minepark\commands;

use minepark\Providers;
use minepark\Components;
use pocketmine\event\Event;
use pocketmine\level\Position;
use minepark\components\chat\GameChat;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\defaults\MapConstants;
use minepark\providers\BankingProvider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\PlayerAttributes;
use minepark\providers\MapProvider;

class JailExitCommand extends Command
{
    private const CURRENT_COMMAND = "jexit";

    private const FREE_PRICE = 50000;

    private const DOOR_DISTANCE = 20;

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
        if($this->getJailPoint($player->getPosition()) == null or !$player->existsAttribute(PlayerAttributes::ARRESTED)) {
            $player->sendMessage("CommandJailExitNoPoint");
            return;
        }
        
        if($this->bankingProvider->takePlayerMoney($player, self::FREE_PRICE)) {
            $this->gameChat->sendLocalMessage($player, "{CommandJailExit}", "§d", self::DOOR_DISTANCE);
            $player->release();
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
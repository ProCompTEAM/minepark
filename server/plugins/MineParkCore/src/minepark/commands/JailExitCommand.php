<?php
namespace minepark\commands;

use minepark\Api;
use minepark\Mapper;

use minepark\Providers;
use pocketmine\event\Event;
use pocketmine\level\Position;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class JailExitCommand extends Command
{
    public const CURRENT_COMMAND = "jexit";

    public const FREE_PRICE = 50000;

    public const DOOR_DISTANCE = 20;

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
        
        if(Providers::getBankingProvider()->takePlayerMoney($player, self::FREE_PRICE)) {
            $this->getCore()->getChatter()->send($player, "{CommandJailExit}", "§d", self::DOOR_DISTANCE);

            $this->getCore()->getApi()->changeAttr($player, "A", false);
            $this->getCore()->getApi()->changeAttr($player, "W", false);

            $this->getCore()->getMapper()->teleportPoint($player, Mapper::POINT_NAME_ADIMINISTRATION);

            $player->getStatesMap()->bar = null;
        } else {
            $player->sendLocalizedMessage("{CommandJailExitNoMoney}". self::FREE_PRICE);
        }
    }

    private function getJailPoint(Position $position) : ?string
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($position, self::DOOR_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == Mapper::POINT_NAME_JAIL) {
                return $point;
            }
        }

        return null;
    }
}
?>
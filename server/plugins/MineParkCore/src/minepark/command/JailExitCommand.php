<?php
namespace minepark\command;

use minepark\Api;
use minepark\Permissions;

use minepark\player\implementations\MineParkPlayer;
use pocketmine\event\Event;
use pocketmine\level\Position;

class JailExitCommand extends Command
{
    public const CURRENT_COMMAND = "jexit";

    public const JAIL_POINT_NAME = "КПЗ";
    public const FREE_POINT_NAME = "Мэрия";

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
        
        if($this->getCore()->getBank()->takePlayerMoney($player, self::FREE_PRICE)) {
            $this->getCore()->getChatter()->send($player, "{CommandJailExit}", "§d", self::DOOR_DISTANCE);

            $this->getCore()->getApi()->changeAttr($player, "A", false);
            $this->getCore()->getApi()->changeAttr($player, "W", false);

            $this->getCore()->getMapper()->teleportPoint($player, self::FREE_POINT_NAME);

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
            if($point == self::JAIL_POINT_NAME) {
                return $point;
            }
        }

        return null;
    }
}
?>
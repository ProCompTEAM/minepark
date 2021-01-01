<?php
namespace minepark\command;

use minepark\player\implementations\MineParkPlayer;
use pocketmine\event\Event;
use pocketmine\level\Position;

use minepark\Permissions;

class CasinoCommand extends Command
{
    public const CURRENT_COMMAND = "casino";

    public const CASINO_POINT_NAME = "Казино";

    public const CASINO_DISTANCE = 10;

    public const CASINO_MIN_SUM = 2000;
    public const CASINO_MAX_SUM = 500000;
    public const CASINO_CHANCE = 3;

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
        if(!$this->checkState($player, $args)) {
            return;
        }

        if($this->getCore()->getBank()->takePlayerMoney($player, $args[0], false))
        {
            if(mt_rand(1, self::CASINO_CHANCE) == 1) {
                $this->getCore()->getBank()->givePlayerMoney($player, $args[0] * 2);
                $player->sendMessage("CommandCasinoWin");
            } else { 
                $player->sendMessage("CommandCasinoLose");
            }
        } else {
          $player->sendMessage("CommandCasinoNoMoney");  
        } 

        $event->setCancelled();
    }

    private function getCasinoPoint(Position $position) : ?string
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($position, self::CASINO_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == self::CASINO_POINT_NAME) {
                return $point;
            }
        }

        return null;
    }

    private function checkState(MineParkPlayer $player, array $args) : bool
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("CommandCasinoUse");
            return false;
        }

        if($this->getCasinoPoint($player->getPosition()) == null) {
            $player->sendMessage("CommandCasinoNear");
            return false;
        }

        if($args[0] < self::CASINO_MIN_SUM or $args[0] > self::CASINO_MAX_SUM) {
            $player->sendLocalizedMessage("{CommandCasinoMoneyMin}" . self::CASINO_MIN_SUM . "{CommandCasinoMoneyMax}" . self::CASINO_MAX_SUM);
            return false;
        }

        return true;
    }
}
?>
<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\event\Event;
use pocketmine\world\Position;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;

class CasinoCommand extends Command
{
    private const CURRENT_COMMAND = "casino";

    private const CASINO_POINT_NAME = "Казино";

    private const CASINO_DISTANCE = 10;

    private const CASINO_MIN_SUM = 2000;
    private const CASINO_MAX_SUM = 500000;
    private const CASINO_CHANCE = 3;
    private const PRIZE_MULTIPLIER = 1;

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
        if (!$this->checkState($player, $args)) {
            return;
        }

        if (Providers::getBankingProvider()->takePlayerMoney($player, $args[0], false)) {
            if (mt_rand(1, self::CASINO_CHANCE) == 1) {
                Providers::getBankingProvider()->givePlayerMoney($player, $args[0] * self::PRIZE_MULTIPLIER);
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
        $pointList = Providers::getMapProvider()->getNearPoints($position, self::CASINO_DISTANCE); 

        foreach($pointList as $point) {
            if ($point === self::CASINO_POINT_NAME) {
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

        if($this->getCasinoPoint($player->getPosition()) === null) {
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
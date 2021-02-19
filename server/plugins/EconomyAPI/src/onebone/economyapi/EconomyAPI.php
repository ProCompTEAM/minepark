<?php
namespace onebone\economyapi;

use minepark\Core;
use minepark\player\Bank;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class EconomyAPI extends PluginBase
{
    public const RET_INVALID = 0;
    public const RET_SUCCESS = 1;

    public static $_instance;

    public function onEnable()
    {
        self::$_instance = $this;
    }

    public static function getInstance() : EconomyAPI
    {
        return self::$_instance;
    }

    public function myMoney(Player $player) : float
    {
        return $this->getBank()->getPlayerMoney($player);
    }

    public function addMoney($player, $amount, bool $force, string $issued="") : int
    {
        if (is_string($player)) {
            $player = $this->getServer()->getPlayer($player);
        }

        if ($this->getBank()->givePlayerMoney($player, $amount)) {
            return self::RET_SUCCESS;
        }

        return self::RET_INVALID;
    }

    public function reduceMoney($player, $amount, bool $force, string $issued) : int
    {
        if (is_string($player)) {
            $player = $this->getServer()->getPlayer($player);
        }

        if ($this->getBank()->givePlayerMoney($player, $amount)) {
            return self::RET_SUCCESS;
        }

        return self::RET_INVALID;
    }

    protected function getCore() : Core
    {
        return Core::getActive();
    }

    protected function getBank() : Bank
    {
        return $this->getCore()->getBank();
    }
}
?>
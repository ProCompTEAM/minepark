<?php
namespace onebone\economyapi;

use minepark\Core;
use pocketmine\player\Player;
use minepark\Providers;
use pocketmine\plugin\PluginBase;

class EconomyAPI extends PluginBase
{
    public static $_instance;

    public function onEnable() : void
    {
        self::$_instance = $this;
    }

    public static function getInstance() : EconomyAPI
    {
        return self::$_instance;
    }

    public function myMoney(Player $player) : float
    {
        return Providers::getBankingProvider()->getPlayerMoney($player);
    }

    public function addMoney($player, $amount, bool $force = false, string $issued = "") : int
    {
        if (is_string($player)) {
            $player = $this->getServer()->getPlayerExact($player);
        }

        return Providers::getBankingProvider()->givePlayerMoney($player, $amount);
    }

    public function reduceMoney($player, $amount, bool $force = false, string $issued = "") : int
    {
        if (is_string($player)) {
            $player = $this->getServer()->getPlayerExact($player);
        }

        return Providers::getBankingProvider()->takePlayerMoney($player, $amount);
    }

    protected function getCore() : Core
    {
        return Core::getActive();
    }
}
?>
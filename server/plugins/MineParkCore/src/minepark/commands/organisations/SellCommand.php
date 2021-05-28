<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use minepark\Providers;
use pocketmine\item\Item;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\GameChat;
use minepark\components\organisations\Organisations;
use minepark\defaults\MapConstants;

class SellCommand extends OrganisationsCommand
{
    private const CURRENT_COMMAND = "sell";

    private const MARKETPLACE_DISTANCE = 15;

    private GameChat $gameChat;

    public function __construct()
    {
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
        if (!self::isSeller($player)) {
            $player->sendMessage("CommandSellNoSeller");
            return;
        }

        $this->gameChat->sendLocalMessage($player, "{CommandSellKey}", "§d : ", 10);

        if (!$this->isShopClose($player)) {
            $player->sendMessage("CommandSellNoShop");
            return;
        }

        $buyers = $this->getBuyersNear($player);

        if (self::argumentsNo($buyers)) {
            $player->sendMessage("CommandSellNoCash");
            return;
        }
        
        $this->handleAllBuyers($buyers, $player);
    }

    public static function isSeller(MineParkPlayer $player) : bool
    {
        return $player->getProfile()->organisation === Organisations::SELLER_WORK or $player->isOp();
    }

    private function isShopClose(MineParkPlayer $player)
    {
        return Providers::getMapProvider()->hasNearPointWithType($player, self::MARKETPLACE_DISTANCE, MapConstants::POINT_GROUP_MARKETPLACE);
    }

    private function getBuyersNear(MineParkPlayer $player)
    {
        $players = $this->getCore()->getRegionPlayers($player, 7);
        $buyers = [];

        foreach($players as $currentPlayer) {
            if(isset($currentPlayer->getStatesMap()->goods[0])) {
                $buyers[] = $currentPlayer;
            }
        }

        return $buyers;
    }

    private function handleAllBuyers(array $buyers, MineParkPlayer $seller)
    {
        foreach($buyers as $buyerId => $buyer) {
            $price = 0;

            foreach($buyer->getStatesMap()->goods as $g) {
                $price = $price + $g[1];
            }

            if(Providers::getBankingProvider()->takePlayerMoney($buyer, $price)) {
                $this->handleSell($price, $buyer, $seller, $buyerId);
            } else {
                $this->notMuchMoney($buyer, $seller, $buyerId);
            }
        }
    }

    private function notMuchMoney(MineParkPlayer $buyer, MineParkPlayer $seller, int $buyerId)
    {
        $seller->sendLocalizedMessage("CommandSellNoMoney1Part1" . ($buyerId + 1) . "CommandSellNoMoney1Part2");
        $buyer->sendMessage("CommandSellNoMoney2");
        $buyer->sendMessage("CommandSellNoMoney3");
        $buyer->getStatesMap()->goods = [];
    }

    private function handleSell(int $price, MineParkPlayer $buyer, MineParkPlayer $seller, int $buyerId)
    {
        $receipt = "§e--==========ЧЕК==========--\n";

        foreach($buyer->getStatesMap()->goods as $good) {
            $item = Item::get($good[0], 0, 1);
            $item->setCustomName($good[2]);
            $buyer->getInventory()->addItem($item);
            $receipt .= "§a".$good[2]." §eза §3".$good[1]." руб\n";
        }

        $buyer->sendMessage($receipt);
        $buyer->sendLocalizedMessage("{CommandSellFinalPart1}" . $price . "{CommandSellFinalPart2}");

        $buyer->getStatesMap()->goods = [];
        Providers::getBankingProvider()->givePlayerMoney($seller, ceil($price/2));

        $seller->sendLocalizedMessage("{CommandSellDo}" . ($buyerId + 1));
    }
}
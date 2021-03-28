<?php
namespace minepark\commands\organisations;

use minepark\commands\base\OrganisationsCommand;
use minepark\Providers;
use pocketmine\item\Item;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;
use minepark\defaults\MapConstants;

class SellCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "sell";

    public const MARKETPLACE_DISTANCE = 15;

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
        $this->getCore()->getChatter()->sendLocalMessage($player, "§8(§dв руках ключ от кассового аппарата§8)", "§d : ", 10);
        $plist = Providers::getMapProvider()->getNearPoints($player->getPosition(), 15);

        if ($this->noPointsNear($plist)) {
            $player->sendMessage("CommandSellKey");
            return;
        }

        if (!$this->ifIsNearShops($player)) {
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
        return $player->getProfile()->organisation == Organisations::SELLER_WORK or $player->isOp();
    }

    private function noPointsNear(array $points) : bool
    {
        return count($points) <= 0;
    }

    private function ifIsNearShops(MineParkPlayer $player)
    {
        return Providers::getMapProvider()->hasNearPointWithType($player, self::MARKETPLACE_DISTANCE, MapConstants::POINT_GROUP_MARKETPLACE);
    }

    private function getBuyersNear(MineParkPlayer $player)
    {
        $players = $this->getCore()->getApi()->getRegionPlayers($player, 7);
        $buyers = array();

        foreach($players as $currentPlayer) {
            if(count($currentPlayer->getStatesMap()->goods) > 0) {
                $buyers[] = $currentPlayer;
            }
        }
        return $buyers;
    }

    private function handleAllBuyers(array $buyers, MineParkPlayer $seller)
    {
        foreach($buyers as $curr => $b) {
            $price = 0;

            foreach($b->getStatesMap()->goods as $g) {
                $price = $price + $g[1];
            }

            if(Providers::getBankingProvider()->takePlayerMoney($b, $price)) {
                $this->handleSell($price, $b, $seller, $curr);
            } else {
                $this->notMuchMoney($b, $seller, $curr);
            }
        }
    }

    private function notMuchMoney(MineParkPlayer $buyer, MineParkPlayer $seller, $curr)
    {
        $seller->sendLocalizedMessage("CommandSellNoMoney1Part1".($curr + 1)."CommandSellNoMoney1Part2");
        $buyer->sendMessage("CommandSellNoMoney2");
        $buyer->sendMessage("CommandSellNoMoney3");
        $buyer->getStatesMap()->goods = array();
    }

    private function handleSell(int $price, MineParkPlayer $b, MineParkPlayer $seller, $curr)
    {
        $receipt = "§e--==========ЧЕК==========--\n";

        foreach($b->getStatesMap()->goods as $g) {
            $item = Item::get($g[0], 0, 1);
            $item->setCustomName($g[2]);
            $b->getInventory()->addItem($item);
            $receipt .= "§a".$g[2]." §eза §3".$g[1]." руб\n";
        }

        $b->sendMessage($receipt);
        $b->sendLocalizedMessage("{CommandSellFinalPart1}".$price."{CommandSellFinalPart2}");

        $b->getStatesMap()->goods = array();
        Providers::getBankingProvider()->givePlayerMoney($seller, ceil($price/2));

        $seller->sendLocalizedMessage("{CommandSellDo}".($curr + 1));
    }
}
?>
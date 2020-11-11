<?php
namespace minepark\modules\organisations\command;

use minepark\modules\organisations\Organisations;
use minepark\Permission;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\Event;

class SellCommand extends OrganisationsCommand
{
    public const CURRENT_COMMAND = "sell";

    public const POINT_GROUP = 2;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if (!self::isSeller($player)) {
            $player->sendMessage("§cВы не продавец!");
            return;
        }
        $this->getCore()->getChatter()->send($player, "§8(§dв руках ключ от кассового аппарата§8)", "§d : ", 10);
        $plist = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 15);

        if ($this->noPointsNear($plist)) {
            $player->sendMessage("§6Вы далеко от торгового учреждения! (/gps)");
            return;
        }

        if (!$this->ifIsNearShops($player)) {
            $player->sendMessage("§6Рядом с вами нет кассового аппарата!");
            return;
        }

        $buyers = $this->getBuyersNear($player);

        if (self::argumentsNo($buyers)) {
            $player->sendMessage("§6Рядом с вами нет покупателей!");
            return;
        }
        
        $this->handleAllBuyers($buyers, $player);
    }

    public static function isSeller(Player $p) : bool
    {
        return $p->getProfile()->organisation == Organisations::SELLER_WORK || $p->isOp();
    }

    private function noPointsNear(array $points) : bool
    {
        return count($points) <= 0;
    }

    private function ifIsNearShops(Player $p)
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($p->getPosition(), 15);

        foreach($plist as $point) {
            $pg = $this->getCore()->getMapper()->getPointGroup($point);

            if($pg == self::POINT_GROUP) {
                return true;
            }

            return false;
        }
    }

    private function getBuyersNear(Player $player)
    {
        $x = $player->getX();
        $y = $player->getY(); 
        $z = $player->getZ();

        $players = $this->getCore()->getApi()->getRegionPlayers($x, $y, $z, 7);
        $buyers = array();

        foreach($players as $p) {
            if(count($p->goods) > 0) {
                $buyers[] = $p;
            }
        }
        return $buyers;
    }

    private function handleAllBuyers(array $buyers, Player $seller)
    {
        foreach($buyers as $curr => $b) {
            $price = 0;

            foreach($b->goods as $g) {
                $price = $price + $g[1];
            }

            if($this->getCore()->getBank()->takePlayerMoney($b, $price)) {
                $this->handleSell($price, $b, $seller, $curr);
            } else {
                $this->notMuchMoney($b, $seller, $curr);
            }
        }
    }

    private function notMuchMoney(Player $buyer, Player $seller, $curr)
    {
        $seller->sendMessage("§eПокупателю #".($curr + 1)." не хватило денег для покупки!");
        $buyer->sendMessage("§cСожалеем, но вам не хватило денег для покупки!");
        $buyer->sendMessage("§eПродавец отобрал у вас корзину с продуктами");
        $buyer->goods = array();
    }

    private function handleSell(int $price, Player $b, Player $seller, $curr)
    {
        $receipt = "§e--==========ЧЕК==========--\n";
        foreach($b->goods as $g) {
            $item = Item::get($g[0], 0, 1);
            $item->setCustomName($g[2]);
            $b->getInventory()->addItem($item);
            $receipt .= "§a".$g[2]." §eза §3".$g[1]." руб\n";
        }
        $b->sendMessage($receipt);
        $b->sendMessage("§eИтого: ".$price." руб");
        $b->goods = array();
        $this->getCore()->getBank()->givePlayerMoney($seller, ceil($price/2));
        $seller->sendMessage("§aВы продали товар покупателю §e#".($curr + 1));
    }
}
?>
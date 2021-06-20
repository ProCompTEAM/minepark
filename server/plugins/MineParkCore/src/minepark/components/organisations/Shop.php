<?php
namespace minepark\components\organisations;

use minepark\Events;
use pocketmine\item\ItemFactory;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use minepark\defaults\EventList;
use pocketmine\event\block\BlockEvent;
use minepark\components\base\Component;

use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;

class Shop extends Component
{
    const MINIMAL_PRICE = 0;
    const MAXIMAL_PRICE = 20000;

    public function initialize()
    {
        Events::registerEvent(EventList::SIGN_CHANGE_EVENT, [$this, "sign"]);
        Events::registerEvent(EventList::PLAYER_INTERACT_EVENT, [$this, "tap"]);

        $this->c = new Config($this->getCore()->getTargetDirectory()."shops.json", Config::JSON);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function tap(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();

        if($block instanceof Sign or $block instanceof SignPost or $block instanceof WallSign) {
            $format = $block->getX() . "_" . $block->getY() . "_" . $block->getZ();

            if($this->c->exists($format)) {
                $this->handleSignTap($format, $event);
            }
        }
    }
    
    public function sign(SignChangeEvent $event)
    {
        $player = $event->getPlayer();

        $lns = $event->getNewText()->getLines();

        if ($lns[0] == "[shop]" and $player->isOperator()) {
            /*
                ||  [shop]  ||
                ||    gId   ||
                ||   price  ||
                ||CustomName||
            */
            if(self::areNormalLines($lns)) {
                $this->handleCreateSign($event, $lns, $player);
                return;
            }

            $player->sendMessage("§cНекорректный формат данных при создании магазина!");
        } elseif($lns[0] == "[shophelp]" and $player->isOperator()) {
            $this->showSignHelp($event);
        }
    }
    
    public static function areNormalLines(array $lns) : bool
    {
        return isset($lns[1]) and isset($lns[2]) and isset($lns[3]) and is_numeric($lns[1]) and is_numeric($lns[2]);
    }

    public static function priceInvalid(int $price)
    {
        return $price <= self::MINIMAL_PRICE OR $price > self::MAXIMAL_PRICE;
    }
    
    private function handleSignTap(string $pos, PlayerInteractEvent $event)
    {
        $price = $this->c->getNested($pos . ".price");
        $label = $this->c->getNested($pos . ".label");
        $id = $this->c->getNested($pos . ".id");

        $res = array($id, $price, $label);

        array_push($event->getPlayer()->getStatesMap()->goods, $res);
    
        $event->getPlayer()->sendMessage("§7[§6!§7] §aВы положили §b$label §aза §b$price §aв корзину.");
    }

    private function handleCreateSign(BlockEvent $event, array $lns, MineParkPlayer $player)
    {
        if(self::priceInvalid($lns[2])) {
            $player->sendMessage("§cЦена товара должна быть в интервале от 0 до 20000");
            return;
        }

        $id = $lns[1]; 
        $price = $lns[2];

        $name = $this->handleItemName(1, $lns[3]);
        
        $this->createSign($event, $name, $price, $id, $player);
    }

    private function createSign(BlockEvent $event, string $name, int $price, int $id, MineParkPlayer $player)
    {
        $x = floor($event->getBlock()->getX());
        $y = floor($event->getBlock()->getY());
        $z = floor($event->getBlock()->getZ());
        $pos = $x."_".$y."_".$z;

        $this->setSignLines($event, $name, $price);
        $this->saveSignInConfig($pos, $price, $id, $name);
        
        $player->sendMessage("§aТочка продажи §3$name §a по цене §d$price §aдобавлена");
    }

    private function setSignLines($event, string $name, int $price)
    {
        $event->setLine(0, "§7[§6нажмите§7]"); 
        $event->setLine(1, "§f(в корзину)");
        $event->setLine(2, "§dЦена: §e$price".'р');
        $event->setLine(3, "§b$name");
    }

    private function saveSignInConfig(string $pos, int $price, int $id, string $name)
    {
        $this->c->setNested("$pos.price",$price);
        $this->c->setNested("$pos.label",$name);
        $this->c->setNested("$pos.id",$id);
        $this->c->save();
    }

    private function handleItemName(int $itemId, string $line) : string
    {
        $item = ItemFactory::getInstance()->get($itemId);
        return $line == "?" ? $item->getName() : $line;
    }

    private function showSignHelp(SignChangeEvent $event)
    {
        $event->setLine(0, "§eПозвать продавца:"); 
        $event->setLine(1, "§b/getseller");
        $event->setLine(2, "§6Очистить корзину:");
        $event->setLine(3, "§8/clear goods");
    }
}
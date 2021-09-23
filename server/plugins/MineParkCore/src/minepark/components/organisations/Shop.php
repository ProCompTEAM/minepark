<?php
namespace minepark\components\organisations;

use minepark\Events;
use pocketmine\block\BaseSign;
use pocketmine\block\FloorSign;
use pocketmine\block\tile\Sign;
use pocketmine\block\utils\SignText;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
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

    private Config $config;

    public function initialize()
    {
        Events::registerEvent(EventList::SIGN_CHANGE_EVENT, [$this, "sign"]);
        Events::registerEvent(EventList::PLAYER_INTERACT_EVENT, [$this, "tap"]);

        $this->config = new Config($this->getCore()->getTargetDirectory() . "shops.json", Config::JSON);
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

        if($block instanceof BaseSign) {
            $format = $block->getPosition()->getX() . "_" . $block->getPosition()->getY() . "_" . $block->getPosition()->getZ();

            if($this->config->exists($format)) {
                $this->handleSignTap($format, $event);
            }
        }
    }
    
    public function sign(SignChangeEvent $event)
    {
        $player = $event->getPlayer();

        $lines = $event->getNewText()->getLines();

        if ($lines[0] == "[shop]" and $player->isOperator()) {
            /*
                ||  [shop]  ||
                ||    gId   ||
                ||   price  ||
                ||CustomName||
            */
            if(self::areNormalLines($lines)) {
                $this->handleCreateSign($event, $lines, $player);
                return;
            }

            $player->sendMessage("§cНекорректный формат данных при создании магазина!");
        } elseif($lines[0] == "[shophelp]" and $player->isOperator()) {
            $this->showSignHelp($event);
        }
    }
    
    public static function areNormalLines(array $lines) : bool
    {
        return isset($lines[1]) and isset($lines[2]) and isset($lines[3]) and is_numeric($lines[1]) and is_numeric($lines[2]);
    }

    public static function priceInvalid(int $price)
    {
        return $price <= self::MINIMAL_PRICE OR $price > self::MAXIMAL_PRICE;
    }
    
    private function handleSignTap(string $pos, PlayerInteractEvent $event)
    {
        $price = $this->config->getNested($pos . ".price");
        $label = $this->config->getNested($pos . ".label");
        $id = $this->config->getNested($pos . ".id");

        $res = [$id, $price, $label];

        array_push($event->getPlayer()->getStatesMap()->goods, $res);
    
        $event->getPlayer()->sendMessage("§7[§6!§7] §aВы положили §b$label §aза §b$price §aв корзину.");
    }

    private function handleCreateSign(SignChangeEvent $event, array $lines, MineParkPlayer $player)
    {
        if(self::priceInvalid($lines[2])) {
            $player->sendMessage("§cЦена товара должна быть в интервале от 0 до 20000");
            return;
        }

        $id = $lines[1]; 
        $price = $lines[2];

        $name = $this->handleItemName(1, $lines[3]);
        
        $this->createSign($event, $name, $price, $id, $player);
    }

    private function createSign(SignChangeEvent $event, string $name, int $price, int $id, MineParkPlayer $player)
    {
        $x = floor($event->getBlock()->getPosition()->getX());
        $y = floor($event->getBlock()->getPosition()->getY());
        $z = floor($event->getBlock()->getPosition()->getZ());
        $pos = $x."_".$y."_".$z;

        $this->setSignLines($event, $name, $price);
        $this->saveSignInConfig($pos, $price, $id, $name);
        
        $player->sendMessage("§aТочка продажи §3$name §a по цене §d$price §aдобавлена");
    }

    private function setSignLines(SignChangeEvent $event, string $name, int $price)
    {
        $text = new SignText([
            "§7[§6нажмите§7]",
            "§f(в корзину)",
            "§dЦена: §e$price".'р',
            "§b$name"
        ]);

        $event->setNewText($text);
    }

    private function saveSignInConfig(string $pos, int $price, int $id, string $name)
    {
        $this->config->setNested("$pos.price",$price);
        $this->config->setNested("$pos.label",$name);
        $this->config->setNested("$pos.id",$id);
        $this->config->save();
    }

    private function handleItemName(int $itemId, string $line) : string
    {
        $item = ItemFactory::getInstance()->get($itemId);
        return $line == "?" ? $item->getName() : $line;
    }

    private function showSignHelp(SignChangeEvent $event)
    {
        $text = new SignText([
            "§eПозвать продавца:",
            "§b/getseller",
            "§6Очистить корзину:",
            "§8/clear goods"
        ]);

        $event->setNewText($text);
    }
}
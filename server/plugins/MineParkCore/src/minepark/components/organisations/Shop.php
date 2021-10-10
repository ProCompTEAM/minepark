<?php
namespace minepark\components\organisations;

use minepark\Events;
use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use minepark\defaults\EventList;
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
        Events::registerEvent(EventList::SIGN_CHANGE_EVENT, [$this, "onSignChange"]);
        Events::registerEvent(EventList::PLAYER_INTERACT_EVENT, [$this, "onInteract"]);

        // TODO: Move to mdc(watch task #516)
        $this->config = new Config($this->getCore()->getTargetDirectory() . "shops.json", Config::JSON);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();

        if($block instanceof BaseSign) {
            $format = $block->getPosition()->getX() . "_" . $block->getPosition()->getY() . "_" . $block->getPosition()->getZ();

            if($this->config->exists($format)) {
                $this->handleSignTap($format, $event);
            }
        }
    }
    
    public function onSignChange(SignChangeEvent $event)
    {
        $player = $event->getPlayer();

        $lines = $event->getNewText()->getLines();

        if($lines[0] == "[shop]" and $player->isOperator()) {
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

            $player->sendMessage("ShopCreateError");
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
    
    private function handleSignTap(string $signPosition, PlayerInteractEvent $event)
    {
        $productInfo = $this->config->get($signPosition);

        $price = $productInfo["price"];
        $label = $productInfo["label"];
        $id = $productInfo["id"];

        $productInfo = [$id, $price, $label];

        array_push($event->getPlayer()->getStatesMap()->goods, $productInfo);
    
        $event->getPlayer()->sendMessage("ShopPutIn");
    }

    private function handleCreateSign(SignChangeEvent $event, array $lines, MineParkPlayer $player)
    {
        if(self::priceInvalid($lines[2])) {
            $player->sendMessage("ShopBadPrice");
            return;
        }

        $id = $lines[1]; 
        $price = $lines[2];

        if(!$this->itemIdExists($id)) {
            $player->sendMessage("ShopItemNoExist");
            return;
        }

        $name = $this->handleItemName($id, $lines[3]);
        
        $this->createSign($event, $name, $price, $id, $player);
    }

    private function createSign(SignChangeEvent $event, string $name, int $price, int $id, MineParkPlayer $player)
    {
        $blockPosition = $event->getBlock()->getPosition();

        $x = $blockPosition->getX();
        $y = $blockPosition->getY();
        $z = $blockPosition->getZ();
        $signPosition = $x . "_" . $y . "_" . $z;

        $this->setSignLines($event, $name, $price);
        $this->saveSignInConfig($signPosition, $price, $id, $name);
        
        $player->sendMessage("ShopCreate");
    }

    private function setSignLines(SignChangeEvent $event, string $name, int $price)
    {
        $text = new SignText([
            "§7[§6нажмите§7]",
            "§f(в корзину)",
            "§dЦена: §e$price" . "р",
            "§b$name"
        ]);

        $event->setNewText($text);
    }

    private function saveSignInConfig(string $signPosition, int $price, int $id, string $label)
    {
        $productInfo = [
            "price" => $price,
            "id" => $id,
            "label" => $label
        ];

        $this->config->set($signPosition, $productInfo);
        $this->config->save();
    }

    private function itemIdExists(int $id)
    {
        return ItemFactory::getInstance()->isRegistered($id);
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
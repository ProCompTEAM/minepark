<?php
namespace minepark\components;

use minepark\Providers;
use minepark\Components;
use minepark\defaults\MapConstants;
use minepark\providers\MapProvider;
use minepark\components\chat\Chat;
use minepark\components\base\Component;
use minepark\providers\BankingProvider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\item\ItemFactory;

class FastFood extends Component
{
    private BankingProvider $bankingProvider;

    private MapProvider $mapProvider;

    private Chat $chat;

    public function initialize()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->mapProvider = Providers::getMapProvider();

        $this->chat = Components::getComponent(Chat::class);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }

    public function command(MineParkPlayer $player)
    {
        $core = $this->getCore();

        if($this->mapProvider->hasNearPointWithType($player->getPosition(), 5, MapConstants::POINT_GROUP_FASTFOOD)) {
            $this->chat->sendLocalMessage($player, "{FastFoodNear}", "§d : ", 10);

            if(Providers::getBankingProvider()->getPlayerMoney($player) >= 50) {
                $core->uiWindows->sendFastfoodWindow($player);
                if($player->isPC) {
                    Providers::getBankingProvider()->takePlayerMoney($player, 50);
                    $this->giveItem($player, mt_rand(0, count($this->getAllGoods()) - 1));
                    $player->sendMessage("FastFoodBoard");
                }
            }
            else {
                $player->sendMessage("FastFoodNoMoney");
            }
        }
        else {
            $player->sendMessage("FastFoodNoNear");
        }
    }
    
    public function getAllGoods()
    {
        return array("§l§cCoca Cola 0.75", "§l§eЧай Lipton 0.5", "§l§5Горячий шоколад", 
        "§l§aКапучино кофе", "§l§3Чипсы Lace с грибами", "§l§dCyXaPiKi RUS EXTRO", 
        "§l§6Напиток молочный Actimel", "§l§7Читос Красти", "§l§9Milky Way", 
        "§l§fКиткат молочный", "§l§2Вода БОНАКВА 0.5");
    }
    
    public function giveItem($player, $goodId)
    {
        $this->chat->sendLocalMessage($player, "FastFoodSound", "§d : ", 18);
        
        $item = ItemFactory::getInstance()->get(0); //ItemFactory::getInstance()->get(<id>)->setCount(<count)
        switch($goodId) {
            case 0: $item = ItemFactory::getInstance()->get(260)->setCount(5); break;  //Coca Cola 0.75
            case 1: $item = ItemFactory::getInstance()->get(360)->setCount(5); break;  //Lipton Yellow Tea
            case 2: $item = ItemFactory::getInstance()->get(364)->setCount(2); break;  //Hot Dark Chocolate
            case 3: $item = ItemFactory::getInstance()->get(264)->setCount(3); break;  //Hot Russiano Coffee
            case 4:								 		  //Lace - fresh onion
            case 5: $item = ItemFactory::getInstance()->get(393)->setCount(3); break;  //CyXaPiKi RUS EXTRO
            case 6: $item = ItemFactory::getInstance()->get(297)->setCount(4); break;  //Mini Pizza *Orion*
            case 7: $item = ItemFactory::getInstance()->get(260)->setCount(4); break;  //FruitJam *CosmiX*
            case 8: 							 		  //*Sweet Milky Way*
            case 9: 							 		  //*Big White KitKat*
            case 10: $item = ItemFactory::getInstance()->get(357)->setCount(3); break; //*Double TWIX*

        }

        $label = $this->getAllGoods()[$goodId];
        $this->chat->sendLocalMessage($player, "FastFoodItemInArm".$label." §8)", "§d : ", 10);

        $player->getInventory()->addItem($item);
    }
    
    public function sign(SignChangeEvent $event)
    {
        $p = $event->getPlayer();
        $lines = $event->getNewText()->getLines();

        if($lines[0] == "[eat]" and $p->isOperator()) {
            $event->setLine(0, "§eТорговый автомат"); 
            $event->setLine(1, "§f[=1=2=3=4=5=6=]");
            $event->setLine(2, "§f[=BUY==CANCEL=]");
            $event->setLine(3, "§l§a/eat");
        }
    }
}
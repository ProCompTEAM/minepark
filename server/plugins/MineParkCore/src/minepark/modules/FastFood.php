<?php
namespace minepark\modules;

use minepark\common\player\MineParkPlayer;
use pocketmine\item\Item;

use minepark\Core;
use minepark\Mapper;

class FastFood
{
	public function getCore() : Core
	{
		return Core::getActive();
	}

	public function command(MineParkPlayer $player)
	{
		$core = $this->getCore();

		if($core->getMapper()->hasNearPointWithType($player, 5, Mapper::POINT_GROUP_FASTFOOD)) {
			$core->getChatter()->send($player, "{FastFoodNear}", "§d : ", 10);

			if($core->getBank()->getPlayerMoney($player) >= 50) {
				$core->uiWindows->sendFastfoodWindow($player);
				if($player->isPC) {
					$core->getBank()->takePlayerMoney($player, 50);
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
		$this->getCore()->getChatter()->send($player, "§8(§dслышен звук торгового автомата§8)", "§d : ", 18);
		
		$item = Item::get(0, 0, 1); //Item::get(<id>,<meta>,<count>)

		switch($goodId) {
			case 0: $item = Item::get(260, 0, 5); break;  //Coca Cola 0.75
			case 1: $item = Item::get(360, 0, 5); break;  //Lipton Yellow Tea
			case 2: $item = Item::get(364, 0, 2); break;  //Hot Dark Chocolate
			case 3: $item = Item::get(264, 0, 3); break;  //Hot Russiano Coffee
			case 4:								 		  //Lace - fresh onion
			case 5: $item = Item::get(393, 0, 3); break;  //CyXaPiKi RUS EXTRO
			case 6: $item = Item::get(297, 0, 4); break;  //Mini Pizza *Orion*
			case 7: $item = Item::get(260, 0, 4); break;  //FruitJam *CosmiX*
			case 8: 							 		  //*Sweet Milky Way*
			case 9: 							 		  //*Big White KitKat*
			case 10: $item = Item::get(357, 0, 3); break; //*Double TWIX*
		}

		$label = $this->getAllGoods()[$goodId];
		$this->getCore()->getChatter()->send($player, "§8(§dв руке товар с надписью ".$label." §8)", "§d : ", 10);

		$player->getInventory()->addItem($item);
	}
	
	public function sign($event)
	{
		$p = $event->getPlayer();
		$lns = $event->getLines();

		if($lns[0] == "[eat]" and $p->isOp()) {
			$event->setLine(0, "§eТорговый автомат"); 
			$event->setLine(1, "§f[=1=2=3=4=5=6=]");
			$event->setLine(2, "§f[=BUY==CANCEL=]");
			$event->setLine(3, "§l§a/eat");
		}
	}
}
?>
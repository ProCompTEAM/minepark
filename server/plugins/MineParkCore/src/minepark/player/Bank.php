<?php
namespace minepark\player;

use minepark\player\implementations\MineParkPlayer;
use onebone\economyapi\EconomyAPI;

class Bank
{
	public const PREFIX = "[BANK] ";
	
	public function getPlayerMoney(MineParkPlayer $player) : int
	{
		return EconomyAPI::getInstance()->myMoney($player);
	}
	
	public function takePlayerMoney(MineParkPlayer $player, int $money, bool $label = true)
	{ 
		$status = EconomyAPI::getInstance()->reduceMoney($player, $money);
	
		if($label and $status) {
				$player->sendMessage(self::PREFIX."§eС вашего счета списано рублей: " . $money);
				$player->sendMessage(self::PREFIX."§bТекущий остаток на карте: " . $this->getPlayerMoney($player) . "руб");
		}
		
		return $status;
	}
	
	public function givePlayerMoney(MineParkPlayer $player, int $money, bool $label = true)
	{ 
		$status = EconomyAPI::getInstance()->addMoney($player, $money);
		
		if($label and $status) {
			$player->sendMessage(self::PREFIX."§aНа ваш счет зачислена сумма в рублях: " . $money);
			$player->sendMessage(self::PREFIX."§2Текущий остаток на карте: §a" . $this->getPlayerMoney($player) . "руб");
		}
		
		return $status;
	}
}
?>
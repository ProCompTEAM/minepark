<?php
namespace minepark\player;

use minepark\Core;
use minepark\mdc\dtos\BankTransactionDto;
use minepark\mdc\dtos\PaymentMethodDto;
use minepark\mdc\sources\BankingSource;
use minepark\player\implementations\MineParkPlayer;

class Bank
{
	public const PREFIX = "[BANK] ";

	public const PAYMENT_METHOD_CASH = 1;
	public const PAYMENT_METHOD_DEBIT = 2;
	public const PAYMENT_METHOD_CREDIT = 3;

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function getPlayerMoney(MineParkPlayer $player) : float
	{
		switch($player->getStatesMap()->paymentMethod) {
			case self::PAYMENT_METHOD_CASH:
				return $this->getCash($player);
			break;
			case self::PAYMENT_METHOD_DEBIT:
				return $this->getDebit($player);
			break;
			case self::PAYMENT_METHOD_CREDIT:
				return $this->getCredit($player);
			break;
		}
	}
	
	public function takePlayerMoney(MineParkPlayer $player, float $money, bool $label = true)
	{
		switch($player->getStatesMap()->paymentMethod) {
			case self::PAYMENT_METHOD_CASH:
				$status = $this->reduceCash($player, $money);
			break;
			case self::PAYMENT_METHOD_DEBIT:
				$status = $this->reduceDebit($player, $money);
			break;
			case self::PAYMENT_METHOD_CREDIT:
				$status = $this->reduceCredit($player, $money);
			break;
		}
	
		if ($label and $status) {
			$player->sendMessage(self::PREFIX."§eС вашего счета списано рублей: " . $money);
			$player->sendMessage(self::PREFIX."§bТекущий остаток на карте: " . $this->getPlayerMoney($player) . "руб");
		}
		
		return $status;
	}
	
	public function givePlayerMoney(MineParkPlayer $player, float $money, bool $label = true)
	{ 
		switch($player->getStatesMap()->paymentMethod) {
			case self::PAYMENT_METHOD_CASH:
				$status = $this->giveCash($player, $money);
			break;
			case self::PAYMENT_METHOD_DEBIT:
				$status = $this->giveDebit($player, $money);
			break;
			case self::PAYMENT_METHOD_CREDIT:
				$status = $this->giveCredit($player, $money);
			break;
		}
		
		if ($label and $status) {
			$player->sendMessage(self::PREFIX."§aНа ваш счет зачислена сумма в рублях: " . $money);
			$player->sendMessage(self::PREFIX."§2Текущий остаток на карте: §a" . $this->getPlayerMoney($player) . "руб");
		}
		
		return $status;
	}

	public function getCash(MineParkPlayer $player) : float
	{
		return $this->getRemoteSource()->getCash($player->getName());
	}

	public function getDebit(MineParkPlayer $player) : float
	{
		return $this->getRemoteSource()->getDebit($player->getName());
	}

	public function getCredit(MineParkPlayer $player) : float
	{
		return $this->getRemoteSource()->getCredit($player->getName());
	}

	public function getAllMoney(MineParkPlayer $player) : float
	{
		return $this->getRemoteSource()->getAllMoney($player->getName());
	}

	public function giveCash(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->giveCash($dto);
	}

	public function giveDebit(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->giveDebit($dto);
	}

	public function giveCredit(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->giveCredit($dto);
	}

	public function reduceCash(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->reduceCash($dto);
	}

	public function reduceDebit(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->reduceDebit($dto);
	}

	public function reduceCredit(MineParkPlayer $player, float $amount) : bool
	{
		$dto = $this->getBankTransactionDto($player->getName(), $amount);
		return $this->getRemoteSource()->reduceCredit($dto);
	}

	public function getPaymentMethod(MineParkPlayer $player) : int
	{
		return $this->getRemoteSource()->getPaymentMethod($player->getName());
	}

	public function switchPaymentMethod(MineParkPlayer $player, int $method) : bool
	{
		$dto = $this->getPaymentMethodDto($player->getName(), $method);

		$status = $this->getRemoteSource()->switchPaymentMethod($dto);

		if ($status) {
			$player->getStatesMap()->paymentMethod = $method;
		}

		return $status;
	}

	public function initializePlayerPaymentMethod(MineParkPlayer $player)
	{
		if ($player->getStatesMap()->isNew) {
			return;
		}

		$player->getStatesMap()->paymentMethod = $this->getRemoteSource()->getPaymentMethod($player->getName());
	}

	private function getPaymentMethodDto(string $userName, int $method) : PaymentMethodDto
	{
		$dto = new PaymentMethodDto;
		$dto->name = $userName;
		$dto->method = $method;
		return $dto;
	}

	private function getBankTransactionDto(string $userName, float $amount) : BankTransactionDto
	{
		$dto = new BankTransactionDto;
		$dto->name = $userName;
		$dto->amount = $amount;
		return $dto;
	}

	private function getRemoteSource() : BankingSource
	{
		return $this->getCore()->getMDC()->getSource(BankingSource::ROUTE);
	}
}
?>
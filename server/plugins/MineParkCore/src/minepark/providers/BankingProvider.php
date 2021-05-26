<?php
namespace minepark\providers;

use minepark\providers\base\Provider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\PaymentMethods;
use minepark\models\dtos\PaymentMethodDto;
use minepark\Providers;
use minepark\providers\data\BankingDataProvider;

class BankingProvider extends Provider
{
    private const PREFIX = "[BANK] ";

    private BankingDataProvider $bankingDataProvider;

    public function __construct()
    {
        $this->bankingDataProvider = Providers::getBankingDataProvider();
    }
    
    public function getPlayerMoney(MineParkPlayer $player) : float
    {
        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                return $this->getCash($player);
            break;
            case PaymentMethods::DEBIT:
                return $this->getDebit($player);
            break;
            case PaymentMethods::CREDIT:
                return $this->getCredit($player);
            break;
        }
    }
    
    public function takePlayerMoney(MineParkPlayer $player, float $money, bool $label = true) : bool
    {
        $status = false;

        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                $status = $this->reduceCash($player, $money);
            break;
            case PaymentMethods::DEBIT:
                $status = $this->reduceDebit($player, $money);
            break;
            case PaymentMethods::CREDIT:
                $status = $this->reduceCredit($player, $money);
            break;
        }
    
        if ($label and $status) {
            $player->sendMessage(self::PREFIX . "§eС вашего счета списано рублей: " . $money);
            $player->sendMessage(self::PREFIX . "§bТекущий остаток на карте: " . $this->getPlayerMoney($player) . "руб");
        }
        
        return $status;
    }
    
    public function givePlayerMoney(MineParkPlayer $player, float $money, bool $label = true) : bool
    { 
        $status = false;

        switch($player->getStatesMap()->paymentMethod) {
            case PaymentMethods::CASH:
                $status = $this->giveCash($player, $money);
            break;
            case PaymentMethods::DEBIT:
                $status = $this->giveDebit($player, $money);
            break;
            case PaymentMethods::CREDIT:
                $status = $this->giveCredit($player, $money);
            break;
        }
        
        if ($label and $status) {
            $player->sendMessage(self::PREFIX . "§aНа ваш счет зачислена сумма в рублях: " . $money);
            $player->sendMessage(self::PREFIX . "§2Текущий остаток на карте: §a" . $this->getPlayerMoney($player) . "руб");
        }
        
        return $status;
    }

    public function getCash(MineParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getCash($player->getName());
    }

    public function getDebit(MineParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getDebit($player->getName());
    }

    public function getCredit(MineParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getCredit($player->getName());
    }

    public function getAllMoney(MineParkPlayer $player) : float
    {
        return $this->bankingDataProvider->getAllMoney($player->getName());
    }

    public function giveCash(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveCash($player->getName(), $amount);
    }

    public function giveDebit(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveDebit($player->getName(), $amount);
    }

    public function giveCredit(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->giveCredit($player->getName(), $amount);
    }

    public function reduceCash(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceCash($player->getName(), $amount);
    }

    public function reduceDebit(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceDebit($player->getName(), $amount);
    }

    public function reduceCredit(MineParkPlayer $player, float $amount) : bool
    {
        return $this->bankingDataProvider->reduceCredit($player->getName(), $amount);
    }

    public function transferDebit(string $userName, string $target, float $amount) : bool
    {
        return $this->bankingDataProvider->transferDebit($userName, $target, $amount);
    }

    public function getPaymentMethod(MineParkPlayer $player) : int
    {
        return $this->bankingDataProvider->getPaymentMethod($player->getName());
    }

    public function switchPaymentMethod(MineParkPlayer $player, int $method) : bool
    {
        $dto = $this->getPaymentMethodDto($player->getName(), $method);

        $status = $this->bankingDataProvider->switchPaymentMethod($dto);

        if ($status) {
            $player->getStatesMap()->paymentMethod = $method;
        }

        return $status;
    }

    public function initializePlayerPaymentMethod(MineParkPlayer $player)
    {
        $player->getStatesMap()->paymentMethod = $this->bankingDataProvider->getPaymentMethod($player->getName());
    }

    private function getPaymentMethodDto(string $userName, int $method) : PaymentMethodDto
    {
        $dto = new PaymentMethodDto;
        $dto->name = $userName;
        $dto->method = $method;
        return $dto;
    }
}
?>
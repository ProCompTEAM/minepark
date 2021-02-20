<?php
namespace minepark\providers\data;

use minepark\models\dtos\PaymentMethodDto;
use minepark\models\dtos\BankTransactionDto;

class BankingSource extends RemoteSource
{
    public const ROUTE = "banking";

    public function getName() : string
    {
        return self::ROUTE;
    }

    public function getCash(string $userName) : ?float
    {
        return (float) $this->createRequest("get-cash", $userName);
    }

    public function getDebit(string $userName) : ?float
    {
        return (float) $this->createRequest("get-debit", $userName);
    }

    public function getCredit(string $userName) : ?float
    {
        return (float) $this->createRequest("get-credit", $userName);
    }

    public function getAllMoney(string $userName) : ?float
    {
        return (float) $this->createRequest("get-all-money", $userName);
    }

    public function giveCash(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("give-cash", $dto);
    }

    public function giveDebit(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("give-debit", $dto);
    }

    public function giveCredit(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("give-credit", $dto);
    }

    public function reduceCash(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("reduce-cash", $dto);
    }

    public function reduceDebit(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("reduce-debit", $dto);
    }

    public function reduceCredit(BankTransactionDto $dto) : bool
    {
        return (bool) $this->createRequest("reduce-credit", $dto);
    }

    public function getPaymentMethod(string $userName) : ?int
    {
        return (int) $this->createRequest("get-payment-method", $userName);
    }

    public function switchPaymentMethod(PaymentMethodDto $dto) : bool
    {
        return (bool) $this->createRequest("switch-payment-method", $dto);
    }

    public function getUnitBalance(string $unitId) : float
    {
        return (float) $this->createRequest("get-unit-balance", $unitId);
    }

    public function initializeUnitBalance() : bool
    {
        return (bool) $this->createRequest("initialize-unit-balance", null);
    }
}
?>
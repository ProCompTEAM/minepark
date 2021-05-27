<?php
namespace minepark\providers\data;

use minepark\models\dtos\PaymentMethodDto;
use minepark\models\dtos\BankTransactionDto;
use minepark\models\dtos\TransferDebitDto;
use minepark\providers\base\DataProvider;

class BankingDataProvider extends DataProvider
{
    public const ROUTE = "banking";

    public function getRoute() : string
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

    public function giveCash(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("give-cash", $dto);
    }

    public function giveDebit(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("give-debit", $dto);
    }

    public function giveCredit(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("give-credit", $dto);
    }

    public function reduceCash(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("reduce-cash", $dto);
    }

    public function reduceDebit(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("reduce-debit", $dto);
    }

    public function reduceCredit(string $userName, float $amount) : bool
    {
        $dto = $this->createBankTransactionDto($userName, $amount);
        return (bool) $this->createRequest("reduce-credit", $dto);
    }

    public function exists(string $userName) : bool
    {
        return (bool) $this->createRequest("exists", $userName);
    }

    public function transferDebit(string $userName, string $target, float $amount)
    {
        return (bool) $this->createRequest("transfer-debit", $this->createTransferDebitDto($userName, $target, $amount));
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

    private function createBankTransactionDto(string $userName, float $amount) : BankTransactionDto
    {
        $dto = new BankTransactionDto;
        $dto->name = $userName;
        $dto->amount = $amount;
        return $dto;
    }

    private function createTransferDebitDto(string $userName, string $target, float $amount) : TransferDebitDto
    {
        $dto = new TransferDebitDto;
        $dto->name = $userName;
        $dto->target = $target;
        $dto->amount = $amount;
        return $dto;
    }
}
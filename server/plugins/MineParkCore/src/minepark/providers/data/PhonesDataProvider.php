<?php
namespace minepark\providers\data;

use minepark\models\dtos\BalanceDto;
use minepark\providers\base\DataProvider;

class PhonesDataProvider extends DataProvider
{
    public const ROUTE = "phones";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function getNumberForUser(string $userName) : ?int
    {
        return (int) $this->createRequest("get-number-for-user", $userName);
    }

    public function getNumberForOrganization(string $organizationName) : ?int
    {
        return (int) $this->createRequest("get-number-for-organization", $organizationName);
    }

    public function createNumberForOrganization(string $organizationName) : int
    {
        return (int) $this->createRequest("create-number-for-organization", $organizationName);
    }

    public function getUserNameByNumber(int $number) : ?string
    {
        return (string) $this->createRequest("get-user-name-by-number", $number);;
    }

    public function getBalance(string $userName) : float
    {
        return (float) $this->createRequest("get-balance", $userName);
    }

    public function addBalance(string $name, float $amount) : bool
    {
        return (bool) $this->createRequest("add-balance", $this->createBalanceDto($name, $amount));
    }

    public function reduceBalance(string $name, float $amount) : bool
    {
        return (bool) $this->createRequest("reduce-balance", $this->createBalanceDto($name, $amount));
    }

    private function createBalanceDto(string $name, float $amount) : BalanceDto
    {
        $dto = new BalanceDto;
        $dto->name = $name;
        $dto->amount = $amount;
        return $dto;
    }
}
?>
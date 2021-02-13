<?php
namespace minepark\mdc\sources;

class PhonesSource extends RemoteSource
{
    public const ROUTE = "phones";

    public function getName() : string
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
}
?>
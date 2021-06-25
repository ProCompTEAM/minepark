<?php
namespace minepark\providers\data;

use minepark\providers\base\DataProvider;

class SettingsDataProvider extends DataProvider
{
    public const ROUTE = "settings";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function getProtocolVersion() : int
    {
        return (int) $this->createRequest("get-protocol-version");
    }

    public function upgradeUnitId(string $unitId)
    {
        $this->createRequest("upgrade-unit-id", $unitId);
    }
}
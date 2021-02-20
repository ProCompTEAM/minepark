<?php
namespace minepark\providers\data;

class SettingsSource extends RemoteSource
{
    public const ROUTE = "settings";

    public function getName() : string
    {
        return self::ROUTE;
    }

    public function upgradeUnitId(string $unitId)
    {
        $this->createRequest("upgrade-unit-id", $unitId);
    }
}
?>
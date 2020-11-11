<?php
namespace minepark\mdc\sources;

class SettingsSource extends RemoteSource
{
    public function getName() : string
    {
        return "settings";
    }

    public function upgradeUnitId(string $unitId)
    {
        $this->createRequest("upgrade-unit-id", $unitId);
    }
}
?>
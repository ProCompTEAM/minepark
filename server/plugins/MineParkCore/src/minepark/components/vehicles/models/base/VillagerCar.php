<?php
namespace minepark\components\vehicles\models\base;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\entity\EntitySizeInfo;
use minepark\components\vehicles\models\base\BaseCar;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

abstract class VillagerCar extends BaseCar
{
    private int $profession = 0;

    public static function getNetworkTypeId() : string
    {
        return EntityIds::VILLAGER;
    }

    public function getProfession() : int
    {
        return $this->profession;
    }

    public function setProfession(int $profession) : void
    {
        $this->profession = $profession;
        $this->networkPropertiesDirty = true;
    }

    public function saveNBT() : CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setInt("Profession", $this->getProfession());
        return $nbt;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties) : void
    {
        parent::syncNetworkData($properties);
        $properties->setInt(EntityMetadataProperties::VARIANT, $this->profession);
    }
}
?>
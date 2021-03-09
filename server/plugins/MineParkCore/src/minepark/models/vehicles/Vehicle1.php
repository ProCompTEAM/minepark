<?php
namespace minepark\models\vehicles;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

class Vehicle1 extends BaseVehicle
{
    public const NETWORK_ID = self::VILLAGER;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);

        $this->propertyManager->setInt(self::DATA_VARIANT, 4);
    }

    public function getLeftSpeed() : float
    {
        return 1.4;
    }

    public function getRightSpeed() : float
    {
        return 1.3;
    }

    public function getForwardAcceleration(): float
    {
        return 0.023;
    }

    public function getBackwardAcceleration(): float
    {
        return 0.015;
    }

    public function getMaxSpeed() : float
    {
        return 1.5;
    }

    public function getReduceMaxSpeed(): float
    {
        return -0.6;
    }

    public function getDriverSeatPosition() : Vector3
    {
        return new Vector3(0.5, 1.4, 0.2);
    }

    public function getPassengerSeatPosition() : Vector3
    {
        return new Vector3(-0.5, 1.4, 0.2);
    }
}
?>
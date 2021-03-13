<?php
namespace minepark\models\vehicles;

use minepark\models\vehicles\base\BaseCar;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

class Car1 extends BaseCar
{
    public const NETWORK_ID = self::VILLAGER;

    public $height = 1.5;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);

        $this->setVillagerProfession(1);
    }

    public function getLeftSpeed() : float
    {
        return 3.1;
    }

    public function getRightSpeed() : float
    {
        return 2.9;
    }

    public function getForwardAcceleration(): float
    {
        return 0.023;
    }

    public function getBackwardAcceleration(): float
    {
        return 0.015;
    }

    public function getBrakeSpeed(): float
    {
        return 0.05;
    }

    public function getVehicleNameTag(): ?string
    {
        return null;
    }

    public function getMaxSpeed() : float
    {
        return 0.55;
    }

    public function getReduceMaxSpeed(): float
    {
        return -0.3;
    }

    public function getCost(): float
    {
        return 10.0;
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
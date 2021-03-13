<?php
namespace minepark\models\vehicles;

use minepark\models\vehicles\base\BaseCar;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

class TaxiCar extends BaseCar
{
    public const NETWORK_ID = self::VILLAGER;

    public $height = 1.5;

    public function __construct(Level $level, CompoundTag $nbt)
    {
        parent::__construct($level, $nbt);
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
        return 0.04;
    }

    public function getBackwardAcceleration(): float
    {
        return 0.03;
    }

    public function getBrakeSpeed(): float
    {
        return 0.06;
    }

    public function getVehicleNameTag(): ?string
    {
        return "§e§e-=TAXI=-";
    }

    public function getMaxSpeed() : float
    {
        return 0.75;
    }

    public function getReduceMaxSpeed(): float
    {
        return -0.38;
    }

    public function getCost(): float
    {
        return 100.0;
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
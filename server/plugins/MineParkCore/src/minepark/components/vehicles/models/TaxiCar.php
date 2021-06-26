<?php
namespace minepark\components\vehicles\models;

use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\nbt\tag\CompoundTag;
use minepark\components\vehicles\models\base\BaseCar;
use minepark\components\vehicles\models\base\VillagerCar;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class TaxiCar extends VillagerCar
{
    public $height = 1.5;

    public function __construct(Location $location, CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
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
        return 0.011;
    }

    public function getBackwardAcceleration(): float
    {
        return 0.007;
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
        return 0.7;
    }

    public function getReduceMaxSpeed(): float
    {
        return -0.3;
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
<?php
namespace minepark\components;

use minepark\components\base\Component;
use minepark\models\vehicles\BaseVehicle;
use minepark\models\vehicles\TaxiVehicle;
use minepark\models\vehicles\Vehicle1;
use minepark\models\vehicles\Vehicle2;
use minepark\models\vehicles\Vehicle3;
use minepark\models\vehicles\Vehicle4;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;

class VehicleManager extends Component
{
    private array $vehicles;

    public function __construct()
    {
        $this->loadVehicles();
    }

    public function getAttributes(): array
    {
        return [
        ];
    }

    public function createVehicle(string $vehicleName, Level $level, Vector3 $pos) : bool
    {
        $vehicleClassName = $this->getVehicle($vehicleName);

        if (!isset($vehicleClassName)) {
            return false;
        }

        (new $vehicleClassName($level, BaseVehicle::createBaseNBT($pos)))->spawnToAll();

        return true;
    }

    public function getVehicle(string $vehicleName) : ?string
    {
        if (!isset($this->getVehicles()[$vehicleName])) {
            return null;
        }

        return $this->getVehicles()[$vehicleName];
    }

    public function getVehicles()
    {
        return $this->vehicles;
    }

    public function handleDataPacketReceive(DataPacketReceiveEvent $event)
    {
        if ($event->getPacket() instanceof PlayerInputPacket) {
            if ($event->getPacket()->motionX === 0 && $event->getPacket()->motionY === 0) {
                return;
            }

            $event->setCancelled();
            $this->handleVehicleMove($event);
        } else if ($event->getPacket() instanceof InteractPacket) {
            if ($event->getPacket()->action !== InteractPacket::ACTION_LEAVE_VEHICLE) {
                return;
            }

            $vehicle = $event->getPlayer()->getLevel()->getEntity($event->getPacket()->target);
			if ($vehicle instanceof BaseVehicle) {
				$vehicle->tryToRemovePlayer($event->getPlayer());
				$event->setCancelled();
			}
        }
    }

    protected function handleVehicleMove(DataPacketReceiveEvent $event)
    {
        if ($event->getPlayer()->getStatesMap()->ridingVehicle?->getDriver()?->getName() !== $event->getPlayer()->getName()) {
			return;
		}

		$vehicle = $event->getPlayer()->getStatesMap()->ridingVehicle;
		
		$vehicle->updateSpeed($event->getPacket()->motionX, $event->getPacket()->motionY);
    }

    private function loadVehicles()
    {
        $this->vehicles = [
            "car1" => Vehicle1::class,
            "car2" => Vehicle2::class,
            "car3" => Vehicle3::class,
            "car4" => Vehicle4::class,
            "taxi" => TaxiVehicle::class
        ];

        foreach ($this->vehicles as $name => $class) {
            BaseVehicle::registerEntity($class);
        }
    }
}
?>
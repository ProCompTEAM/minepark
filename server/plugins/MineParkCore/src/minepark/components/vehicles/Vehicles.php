<?php
namespace minepark\components\vehicles;

use minepark\Events;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use minepark\components\vehicles\models\base\BaseCar;
use minepark\components\vehicles\models\GuestCar1;
use minepark\components\vehicles\models\GuestCar2;
use minepark\components\vehicles\models\GuestCar3;
use minepark\components\vehicles\models\GuestCar4;
use minepark\components\vehicles\models\TaxiCar;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;

class Vehicles extends Component
{
    private array $vehicles;

    public function initialize()
    {
        Events::registerEvent(EventList::DATA_PACKET_RECEIVE_EVENT, [$this, "handleDataPacketReceive"]);
        Events::registerEvent(EventList::PLAYER_QUIT_EVENT, [$this, "processPlayerQuitEvent"]);

        $this->loadVehicles();
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function processPlayerQuitEvent(PlayerQuitEvent $event)
    {
        $player = MineParkPlayer::cast($event->getPlayer());

        if (isset($player->getStatesMap()->ridingVehicle)) {
            $player->getStatesMap()->ridingVehicle->tryToRemovePlayer($player);
        }

        if (isset($player->getStatesMap()->rentedVehicle)) {
            $player->getStatesMap()->rentedVehicle->removeRentedStatus();
        }
    }

    public function createVehicle(string $vehicleName, Level $level, Vector3 $pos, float $yaw) : bool
    {
        $vehicleClassName = $this->getVehicle($vehicleName);

        if (!isset($vehicleClassName)) {
            return false;
        }

        (new $vehicleClassName($level, BaseCar::createBaseNBT($pos, null, $yaw)))->spawnToAll();

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
            if ($event->getPacket()->motionX === 0 and $event->getPacket()->motionY === 0) {
                return;
            }

            $event->setCancelled();
            $this->handleVehicleMove($event);
        } else if ($event->getPacket() instanceof InteractPacket) {
            if ($event->getPacket()->action !== InteractPacket::ACTION_LEAVE_VEHICLE) {
                return;
            }

            $vehicle = $event->getPlayer()->getLevel()->getEntity($event->getPacket()->target);
            if ($vehicle instanceof BaseCar) {
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
            "car1" => GuestCar1::class,
            "car2" => GuestCar2::class,
            "car3" => GuestCar3::class,
            "car4" => GuestCar4::class,
            "taxi" => TaxiCar::class
        ];

        foreach ($this->getVehicles() as $name => $class) {
            BaseCar::registerEntity($class);
        }
    }
}
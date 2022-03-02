<?php

namespace minepark\components\vehicles\models\base;

use jojoe77777\FormAPI\SimpleForm;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\VehicleConstants;
use minepark\models\data\PassengerSeat;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\Rail;
use pocketmine\block\utils\RailConnectionInfo;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityLink;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

abstract class BaseTrain extends BaseVehicle
{
    public $gravity = 1.0;

    public $stepHeight = 1;

    protected ?MineParkPlayer $driver;
    protected array $passengersSeats;

    private int $direction;

    private int $lastX;

    private int $lastZ;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);

        $this->driver = null;
        $this->lastX = 0;
        $this->lastZ = 0;
        $this->generatePassengerSeats();

        if($this->getRailBelow() === null) {
            $this->kill();
            return;
        }
        $this->getNetworkProperties()->setByte(EntityMetadataProperties::MINECART_HAS_DISPLAY, 1);
        $this->getNetworkProperties()->setInt(EntityMetadataProperties::MINECART_DISPLAY_OFFSET, 6);

        $this->restoreDirection();
    }

    protected function getInitialSizeInfo() : EntitySizeInfo
    {
        return new EntitySizeInfo($this->getTrainWidth(), $this->getTrainHeight());
    }

    public function getName() : string
    {
        return "Train";
    }

    public function getDriver() : ?MineParkPlayer
    {
        return $this->driver;
    }

    public function getPassengerSeats() : array
    {
        return $this->passengersSeats;
    }

    public function setDriver(MineParkPlayer $player, bool $force = false) : bool
    {
        if($this->getDriver() !== null) {
            if($force) {
                $this->removeDriver();
            } else {
                return false;
            }
        }

        $this->driver = $player;

        $this->updateUserFlags($player, true, true, $this->getDriverSeatPosition());
        $this->broadcastEntityLink($player, EntityLink::TYPE_RIDER);

        $player->getStatesMap()->ridingVehicle = $this;

        $player->sendTip("Вы водите поезд");

        return true;
    }

    public function addPassenger(MineParkPlayer $player) : bool
    {
        $seatIndex = $this->getFreePassengerSeatIndex();

        if($seatIndex === null) {
            return false;
        }

        $passengerSeat = $this->passengersSeats[$seatIndex];

        $this->updateUserFlags($player, true, false, $passengerSeat->vector);
        $this->broadcastEntityLink($player, EntityLink::TYPE_PASSENGER);

        $this->passengersSeats[$seatIndex]->passenger = $player;

        $player->getStatesMap()->ridingVehicle = null;

        $player->sendTip("Вы сели в поезд");

        return true;
    }

    public function removeDriver() : bool
    {
        if($this->driver === null) {
            return false;
        }

        $this->broadcastEntityLink($this->getDriver(), EntityLink::TYPE_REMOVE);
        $this->updateUserFlags($this->getDriver(), false, true);

        $this->getDriver()->sendTip("Вы больше не водите поезд.");

        $this->driver->getStatesMap()->ridingVehicle = null;

        $this->driver = null;

        return true;
    }

    public function removePassenger(MineParkPlayer $player) : bool
    {
        $removeStatus = false;

        for($i = 0; $i < count($this->passengersSeats); $i++) {
            $passengerSeat = $this->passengersSeats[$i];

            if($passengerSeat->passenger?->getName() === $player->getName()) {
                $removeStatus = true;
                $this->passengersSeats[$i]->passenger = null;
                break;
            }
        }

        if(!$removeStatus) {
            return false;
        }

        $this->broadcastEntityLink($player, EntityLink::TYPE_REMOVE);
        $this->updateUserFlags($player, false, false);

        $player->getStatesMap()->ridingVehicle = null;

        $player->sendTip("Вы вышли из поезда.");

        return true;
    }

    public function tryToRemovePlayer(MineParkPlayer $player) : bool
    {
        if($this->getDriver()?->getName() === $player->getName()) {
            $this->removeDriver();
            return true;
        }

        if($this->removePassenger($player)) {
            return true;
        }

        return false;
    }

    public function removeAllPlayers()
    {
        $this->removeDriver();

        foreach($this->passengersSeats as $passengerSeat) {
            if($passengerSeat->passenger === null) {
                continue;
            }

            $this->removePassenger($passengerSeat->passenger);
        }
    }

    abstract public function getTrainWidth() : float;

    abstract public function getTrainHeight() : float;

    abstract public function getDriverSeatPosition() : Vector3;

    abstract public function getPassengerSeatsVectors() : array;

    public function kill() : void
    {
        $this->removeAllPlayers();

        parent::kill();
    }

    public function attack(EntityDamageEvent $event) : void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager() instanceof MineParkPlayer) {
                $this->sendMenuForm($event->getDamager());
                return;
            }
        }

        parent::attack($event);
    }

    public function onInteract(Player $player, Vector3 $clickPosition) : bool
    {
        $player = MineParkPlayer::cast($player);

        return $this->sendMenuForm($player);
    }

    public function sendMenuForm(MineParkPlayer $player) : bool
    {
        if(isset($player->getStatesMap()->ridingVehicle)) {
            $player->sendMessage("Судя по всему, Вы уже находитесь в каком-то транспорте.");
            return false;
        }

        $menuForm = new SimpleForm([$this, "processMenuForm"]);
        $menuForm->setTitle("Взаимодействие с поездом");

        $menuForm->addButton("Управлять поездом");
        $menuForm->addButton("Сесть на пассажирское место");

        $player->sendForm($menuForm);

        return true;
    }

    public function processMenuForm(MineParkPlayer $player, ?int $answer = null)
    {
        if(is_null($answer)) {
            return;
        }

        if($answer !== 0 and $answer !== 1 and $answer !== 2) {
            $player->sendMessage("Произошла ошибка. Пожалуйста, попробуйте позже.");
            return;
        }

        if($answer === 0) {
            $status = $this->setDriver($player);

            if(!$status) {
                $player->sendMessage("Увы, на месте водителя кто-то находится.");
            }
        } elseif($answer === 1) {
            $status = $this->addPassenger($player);

            if(!$status) {
                $player->sendMessage("Увы, все пассажирские места в поезде заполнены.");
            }
        }
    }

    public function updateSpeed(float $motionX, float $motionZ)
    {
        if($motionX === 0.0 and $motionZ === 0.0) {
            return;
        }

        $this->server->getLogger()->info("x is " . $motionX . " and z is " . $motionZ);

        $this->moveTrain();
    }

    private function generatePassengerSeats()
    {
        $this->passengersSeats = [];

        foreach($this->getPassengerSeatsVectors() as $vector) {
            $passengerSeat = new PassengerSeat();

            $passengerSeat->vector = $vector;
            $passengerSeat->passenger = null;

            array_push($this->passengersSeats, $passengerSeat);
        }
    }

    private function broadcastEntityLink(MineParkPlayer $player, int $type)
    {
        $pk = new SetActorLinkPacket();
        $pk->link = new EntityLink($this->getId(), $player->getId(), $type, true, true);

        foreach($this->getViewers() as $viewer) {
            $viewer->getNetworkSession()->sendDataPacket($pk);
        }
    }

    private function updateUserFlags(MineParkPlayer $player, bool $status, bool $driver, ?Vector3 $seatPosition = null)
    {
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::RIDING, $status);
        $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SITTING, $status);

        if($driver) {
            $player->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::WASD_CONTROLLED, $status);

            if($status) {
                $player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $seatPosition);
            }
        } elseif($status) {
            $player->getNetworkProperties()->setVector3(EntityMetadataProperties::RIDER_SEAT_POSITION, $seatPosition);
        }

        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::SADDLED, $status);
    }

    private function getFreePassengerSeatIndex() : ?int
    {
        for($i = 0; $i < count($this->passengersSeats); $i++) {
            $passengerSeat = $this->passengersSeats[$i];

            if($passengerSeat->passenger === null) {
                return $i;
            }
        }

        return null;
    }

    private function getRailBelow() : ?Rail
    {
        $location = $this->getLocation();

        $x = floor($location->getX());
        $y = floor($location->getY());
        $z = floor($location->getZ());

        $block = $this->getWorld()->getBlockAt($x, $y, $z, true, false);

        if(!$block instanceof Rail) {
            $block = $this->getWorld()->getBlockAt($x, $y - 1, $z, true, false);
        }

        if(!$block instanceof Rail) {
            $block = $this->getWorld()->getBlockAt($x, $y + 1, $z, true, false);
        }

        return $block instanceof Rail ? $block : null;
    }

    private function moveTrain()
    {
        $this->checkRailBelow();

        if(!$this->isAlive()) {
            return;
        }

        if($this->direction === VehicleConstants::TRAIN_DIRECTION_NORTH) {
            $this->setMotion(new Vector3(0, 0, -VehicleConstants::TRAIN_DEFAULT_SPEED));
        } elseif($this->direction === VehicleConstants::TRAIN_DIRECTION_SOUTH) {
            $this->setMotion(new Vector3(0, 0, VehicleConstants::TRAIN_DEFAULT_SPEED));
        } elseif($this->direction === VehicleConstants::TRAIN_DIRECTION_WEST) {
            $this->setMotion(new Vector3(-VehicleConstants::TRAIN_DEFAULT_SPEED, 0, 0));
        } elseif($this->direction === VehicleConstants::TRAIN_DIRECTION_EAST) {
            $this->setMotion(new Vector3(VehicleConstants::TRAIN_DEFAULT_SPEED, 0, 0));
        }
    }

    private function restoreDirection()
    {
        $this->getWorld()->getServer()->getLogger()->info("Yaw " . $this->getLocation()->getYaw() . " and shape " . $this->getRailBelow()->getShape());

        $direction = VehicleConstants::getDirectionByYaw($this->getRailBelow()->getShape(), $this->getLocation()->getYaw());

        if($direction === -1) {
            $this->tryToRestoreYaw();
            return;
        }

        $this->getWorld()->getServer()->getLogger()->info("Initialized with " . $direction);

        $this->direction = $direction;
    }

    private function tryToRestoreYaw()
    {
        $railShape = $this->getRailBelow()->getShape();

        if($railShape !== 0 and $railShape !== 1) {
            $this->kill();
            return;
        }

        $yaw = $this->getLocation()->getYaw();

        if($railShape === 0) {
            if($yaw > 270 or $yaw < 90) {
                $this->setRotation(0, 0);
                $this->direction = VehicleConstants::TRAIN_DIRECTION_SOUTH;
            } else {
                $this->setRotation(180, 0);
                $this->direction = VehicleConstants::TRAIN_DIRECTION_NORTH;
            }
        } else {
            if($yaw > 0 and $yaw < 180) {
                $this->setRotation(90, 0);
                $this->direction = VehicleConstants::TRAIN_DIRECTION_EAST;
            } else {
                $this->setRotation(270, 0);
                $this->direction = VehicleConstants::TRAIN_DIRECTION_WEST;
            }
        }
    }

    private function checkRailBelow()
    {
        $rail = $this->getRailBelow();

        if($rail === null) {
            $this->getDriver()?->sendMessage("Рельс больше нет. Поезд уничтожен.");
            $this->kill();
            return;
        }

        $railX = $rail->getPosition()->getX();
        $railZ = $rail->getPosition()->getZ();

        if($this->lastX === $railX and $this->lastZ === $railZ) {
            return;
        }

        $this->lastX = $railX;
        $this->lastZ = $railZ;

        $newDirection = VehicleConstants::getRailDirections($this->direction)[$rail->getShape()];

        if($this->direction === $newDirection) {
            return;
        }

        $this->setRotation(VehicleConstants::getRailRotations($newDirection)[$rail->getShape()], 0);

        $this->direction = $newDirection;
    }
}
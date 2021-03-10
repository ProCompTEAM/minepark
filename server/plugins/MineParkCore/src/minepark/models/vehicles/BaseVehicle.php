<?php
namespace minepark\models\vehicles;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\entity\Vehicle;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\nbt\tag\CompoundTag;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\Defaults;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\types\EntityLink;
use pocketmine\network\mcpe\protocol\SetActorLinkPacket;

abstract class BaseVehicle extends Vehicle
{
    public $gravity = 1.0;

	public $width = 1.0;

	public $height = 1.0;

	public $baseOffset = 0.0;

    protected ?MineParkPlayer $driver;
    protected ?MineParkPlayer $passenger;

    private float $speed;
    private int $lastMotionSet;

    public function __construct(Level $level, CompoundTag $nbt){
        parent::__construct($level, $nbt);

        $this->driver = null;
        $this->passenger = null;
        $this->speed = 0.0;
        $this->lastMotionSet = 0;

        $this->setCanSaveWithChunk(true);
        $this->saveNBT();
    }

    public function getDriver() : ?MineParkPlayer
    {
        return $this->driver;
    }

    public function getPassenger() : ?MineParkPlayer
    {
        return $this->passenger;
    }

    public function performAction(MineParkPlayer $player, ?int $data = null)
    {
        if (is_null($data)) {
            return;
        }

        $choice = $data + 1;

        if ($choice === Defaults::VEHICLE_ACTION_BE_DRIVER) {
            $this->trySetPlayerDriver($player);
        } else if ($choice === Defaults::VEHICLE_ACTION_BE_PASSENGER) {
            $this->trySetPlayerPassenger($player);
        }
    }

    public function attack(EntityDamageEvent $event) : void
    {
        // check if this entity is being attacked by player
        if ($event instanceof EntityDamageByEntityEvent) {
            $this->handleInteract($event->getDamager());
            return;
        }

        parent::attack($event);
    }

    public function setDriver(MineParkPlayer $player, bool $force = false) : bool
    {
        // $force means if we have to remove current driver
        if ($force) {
            $this->removeDriver();
        }

        if (!is_null($this->getDriver())) {
            return false;
        }

        $this->updateUserFlags($player, true);

        $this->driver = $player;
        $this->getDriver()->getStatesMap()->ridingVehicle = $this;

        $this->broadcastLink($this->getDriver(), EntityLink::TYPE_RIDER);
        return true;
    }

    public function setPassenger(MineParkPlayer $player, bool $force = false) : bool
    {
        if ($force) {
            $this->removePassenger();
        }

        if (!is_null($this->getPassenger())) {
            return false;
        }

        $this->updateUserFlags($player, true, false);

        $this->passenger = $player;
        $this->getPassenger()->getStatesMap()->ridingVehicle = $this;

        $this->broadcastLink($this->getPassenger(), EntityLink::TYPE_PASSENGER);
        return true;
    }

    public function tryToRemovePlayer(MineParkPlayer $player) : bool
    {
        if ($this->passenger?->getName() === $player->getName()) {
            return $this->removePassenger();
        } else if ($this->driver?->getName() === $player->getName()) {
            return $this->removeDriver();
        }

        return false;
    }

    public function removeDriver() : bool
    {
        if (is_null($this->driver)) {
            return false;
        }

        if ($this->driver?->isOnline()) {
            $this->updateUserFlags($this->driver, false);

            $this->broadcastLink($this->driver, EntityLink::TYPE_REMOVE);

            $this->driver->getStatesMap()->ridingVehicle = null;
        }

        $this->driver = null;

        return true;
    }

    public function removePassenger() : bool
    {
        if (is_null($this->passenger)) {
            return false;
        }

        if ($this->passenger?->isOnline()) {
            $this->updateUserFlags($this->passenger, false, false);

            $this->broadcastLink($this->passenger, EntityLink::TYPE_REMOVE);

            $this->passenger->getStatesMap()->ridingVehicle = null;
        }

        $this->passenger = null;

        return true;
    }

    public function updateSpeed(float $x, float $y)
    {
        if($x > 0.0) {
            $this->yaw -= $x * $this->getLeftSpeed();
        } else {
            $this->yaw -= $x * $this->getRightSpeed();
        }

        if ($y === 0.0) {
            return;
        }

        if ($y > 0 && $this->speed <= $this->getMaxSpeed()) {
            $this->addSpeed($this->getForwardAcceleration());
        } else if ($this->speed > $this->getReduceMaxSpeed()) {
            if ($this->speed > 0) {
                $this->reduceSpeed($this->getBrakeSpeed());
            } else {
                $this->reduceSpeed($this->getBackwardAcceleration());
            }
        }
	}

    public function addSpeed(float $speed)
    {
        $calculatedSpeed = $this->speed + $speed;

        if ($calculatedSpeed <= $this->getMaxSpeed()) {
            $this->speed = $calculatedSpeed;
        } else {
            $this->speed = $this->getMaxSpeed();
        }
    }

    public function reduceSpeed(float $speed)
    {
        $calculatedSpeed = $this->speed - $speed;

        if ($calculatedSpeed >= $this->getReduceMaxSpeed()) {
            $this->speed -= $speed;
        } else {
            $this->speed = $this->getReduceMaxSpeed();
        }
    }

    public function getBlockForward(Vector3 $directionVector) : ?Block
    {
        $vector3 = $this->asVector3()->add($directionVector->getX(), $directionVector->getY(), $directionVector->getZ());

        $block1 = $this->getLevel()->getBlock($vector3, false, false);
        $block2 = $this->getLevel()->getBlock($vector3->add(0,1,0), false, false);

        if ($block1->getId() === Block::TALL_GRASS) {
            return null;
        }
        
        if ($block2->getId() !== Block::TALL_GRASS && !$block2->canPassThrough()) {
            return null;
        }

        return !$block1->canPassThrough() ? $block1 : null;
    }

    public function getBlocksAround() : array
    {
        $vectors = [
            new Vector3($this->getX() + 1, $this->getY(), $this->getZ()),
            new Vector3($this->getX() - 1, $this->getY(), $this->getZ()),
            new Vector3($this->getX(), $this->getY(), $this->getZ() + 1),
            new Vector3($this->getX(), $this->getY(), $this->getZ() - 1)
        ];

        $blocks = [];
        
        foreach($vectors as $vector) {
            $block = $this->getLevel()->getBlockAt($vector->getX(), $vector->getY(), $vector->getZ(), false, false);

            if ($block->getId() !== Block::AIR) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }
    
    public function onUpdate(int $currentTick) : bool
    {
        if ($this->speed === 0) {
            return parent::onUpdate($currentTick);
        }

        if ($this->speed <= 0.003 and $this->speed >= -0.003) {
            $this->speed = 0;
            return parent::onUpdate($currentTick);
        }
        
        if ($this->speed > 0) {
            $this->reduceSpeed(0.002);
            $this->performForwardAcceleration();
        } else {
            $this->addSpeed(0.002);
            $this->performBackwardAcceleration();
        }

        parent::onUpdate($currentTick);

        return true;
    }

    abstract public function getLeftSpeed() : float;

    abstract public function getRightSpeed() : float;

    abstract public function getForwardAcceleration() : float;

    abstract public function getBackwardAcceleration() : float;

    abstract public function getMaxSpeed() : float;

    abstract public function getReduceMaxSpeed() : float;

    abstract public function getBrakeSpeed() : float;

    abstract public function getDriverSeatPosition() : Vector3;

    abstract public function getPassengerSeatPosition() : Vector3;

    protected function trySetPlayerDriver(MineParkPlayer $player)
    {
        if (!$this->setDriver($player)) {
            return $player->sendMessage("На данный момент есть человек, сидящий за рулем");
        } else {
            return $player->sendTip("Вы успешно сели за руль!");
        }
    }

    protected function trySetPlayerPassenger(MineParkPlayer $player)
    {
        if (!$this->setPassenger($player)) {
            return $player->sendMessage("На данный момент есть человек, сидящий на пассажирском кресле");
        } else {
            return $player->sendTip("Вы успешно сели в машину!");
        }
    }

    protected function broadcastLink(MineParkPlayer $player, int $type = EntityLink::TYPE_RIDER)
    {
        foreach($this->getViewers() as $viewer) {
			if (!isset($viewer->getViewers()[$player->getLoaderId()])) {
				$player->spawnTo($viewer);
			}

			$pk = new SetActorLinkPacket();
			$pk->link = new EntityLink($this->getId(), $player->getId(), $type, true, true);

			$viewer->sendDataPacket($pk);
		}
    }

    private function performForwardAcceleration()
    {
        $motion = $this->getDirectionVector();

        $motionX = $motion->getX();
        $motionY = $motion->getY();
        $motionZ = $motion->getZ();

        $block = $this->getBlockForward($motion);

        if (isset($block)) {
            $motionY += 2.5;
        }

        $motionX *= $this->speed;
        $motionZ *= $this->speed;

        $this->motion = new Vector3($motionX, $motionY, $motionZ);
    }

    private function performBackwardAcceleration()
    {
        $motion = $this->getDirectionVector();

        $motionX = $motion->getX();
        $motionY = $motion->getY();
        $motionZ = $motion->getZ();

        $motionX *= $this->speed;
        $motionZ *= $this->speed;

        $this->motion = new Vector3($motionX, $motionY, $motionZ);
    }

    private function updateUserFlags(MineParkPlayer $player, bool $status, bool $driver=true)
    {
        $player->setGenericFlag(self::DATA_FLAG_RIDING, $status);
		$player->setGenericFlag(self::DATA_FLAG_SITTING, $status);

        if ($driver) {
		    $player->setGenericFlag(self::DATA_FLAG_WASD_CONTROLLED, $status);

            if ($status) {
                $player->getDataPropertyManager()->setVector3(self::DATA_RIDER_SEAT_POSITION, $this->getDriverSeatPosition());
            }
        } else if ($status) {
            $player->getDataPropertyManager()->setVector3(self::DATA_RIDER_SEAT_POSITION, $this->getPassengerSeatPosition());
        }

        $this->setGenericFlag(self::DATA_FLAG_SADDLED, $status);
    }

    private function handleInteract(MineParkPlayer $player)
    {
        if ($player->getStatesMap()->ridingVehicle) {
            return $player->sendMessage("Вы не сможете перепрыгнуть с одной машины в другую.");
        }

        if (isset($this->driver) && isset($this->passenger)) {
            return $player->sendMessage("В этой машине уже все сидения заняты.");
        }

        $form = new SimpleForm([$this, "performAction"]);
        $form->setTitle("Машина");

        $form->setContent("Выберите, что вы хотите сделать с машиной!");
        $form->addButton("Водить машиной");
        $form->addButton("Стать пассажиром");

        $player->sendForm($form);
    }
}
?>
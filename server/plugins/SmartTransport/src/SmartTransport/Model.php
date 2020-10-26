<?php
namespace SmartTransport;

use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\Player;

use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;


class Model
{
	public const EMPTY_MODEL = "Model";
	public const TAXI_MODEL = "Taxi";
	public const CAR1_MODEL = "Car1";
	public const CAR2_MODEL = "Car2";
	public const CAR3_MODEL = "Car3";
	public const CAR4_MODEL = "Car4";
	public const TRAIN_MODEL = "Train";
	
	private $name;
	
	protected $maxSpeed;
	
	protected $isFlying;
	protected $isTrain;
	
	private $driver;
	private $passengers;
	private $speed;
	private $position;
	
	
	public function __construct(string $modelName)
	{
		$this->name = $modelName;
		
		$this->isFlying = false;
		$this->isTrain = false;
		$this->maxSpeed = 100;
		
		$this->driver = null;
		$this->passengers = array();
		$this->speed = 0;
	}
	
	public function getName() : string
	{
		return $this->name;
	}
	
	public function createEntity(Position $pos, int $rotation)
	{
		return null;
	}
	
	protected function getNBT() : CompoundTag{
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", -90),
				new FloatTag("", -90)
			]),
		]);
		return $nbt;
	}
	
	public function getDriver() : ?Player
	{
		return $this->driver;
	}
	
	public function setDriver(?Player $player)
	{
		$this->driver = $player;
	}
	
	public function getPosition() : ?Position
	{
		return $this->position;
	}
	
	public function setPosition(Position $pos)
	{
		$this->position = &$pos;
	}
	
	public function getSpeed() : int
	{
		return (int) $this->speed;
	}
	
	public function addSpeed()
	{
		if($this->speed < $this->maxSpeed) $this->speed += 0.25;
		else $this->subSpeed();
	}
	
	public function subSpeed()
	{
		if($this->speed >= 20) $this->speed -= 10;
		else $this->speed = 0;
	}
	
	public function clearSpeed()
	{
		$this->speed = 0;
	}
	
	public function trainIs() : bool
	{
		return $this->isTrain;
	}
	
	public function addPerson(Player $player)
	{
		array_push($this->passengers, $player);
	}
	
	public function removePerson(Player $player)
	{
		if(array_search($player, $this->passengers) != -1) 
			unset($this->passengers[array_search($player, $this->passengers)]);
	}
	
	public function getPassengers() : array
	{
		return $this->passengers;
	}
}
?>
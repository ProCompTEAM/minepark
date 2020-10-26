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

use SmartTransport\Model;

class Car4Model extends Model
{
	public function __construct()
	{
		parent::__construct(parent::CAR4_MODEL);
		
		$this->isFlying = false;
		$this->isTrain = false;
		$this->maxSpeed = 50;
	}
	
	public function createEntity(Position $pos, int $rotation) : Entity
	{
		$e = new \pocketmine\entity\Villager($pos->getLevel(), $this->getNBT());
		 
		$e->setPosition($pos);
		$e->setRotation($rotation, 0);
		
		$e->setProfession(4);
		
		$e->spawnToAll();
		
		return $e;
	}
}
?>
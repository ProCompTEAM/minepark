<?php
namespace minepark;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\level\Position;

class Mapper
{
	public $mappoints;
	
	public function __construct()
	{
		$this->mappoints = new Config($this->getCore()->getTargetDirectory() . "points.json", Config::JSON);
	}
	
	public const UNKNOWN_POINT_TYPE = 0;
	public const REALTY_POINT_TYPE = 1;
	public const MARKETPLACE_POINT_TYPE = 2;
	public const SERVICE_POINT_TYPE = 3;
	public const PHONESTREAM_POINT_TYPE = 4;
	public const WORK1_POINT_TYPE = 5;
	public const WORK2_POINT_TYPE = 6;
	public const TRAINSTATION_POINT_TYPE = 7;
	public const ZOO_POINT_TYPE = 8;

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function addPoint(Position $pos, string $name, int $group = 0)
	{
		$c = $this->mappoints;

		$c->setNested("$name.x", floor($pos->getX()));
		$c->setNested("$name.y", floor($pos->getY()));
		$c->setNested("$name.z", floor($pos->getZ()));
		$c->setNested("$name.group", $group);
		$c->save();
	}
	
	public function removePoint(string $name) : bool
	{
		$c = $this->mappoints;

		if($c->exists($name)) {
			$c->remove($name);
			return true;
		} else {
			return false;
		}
	}
	
	public function teleportPoint(Player $player, string $name)
	{
		$c = $this->mappoints;

		if(!$c->exists($name)) {
			$player->sendMessage("MapperInvalidPoint");
		} else {
			$x = $c->getNested("$name.x");
			$y = $c->getNested("$name.y");
			$z = $c->getNested("$name.z");
			$player->teleport(new Vector3($x,$y+1,$z));
		}
	}
	
	public function getPointPosition(string $name) : ?Position
	{
		$c = $this->mappoints;

		if(!$c->exists($name)) {
			return null;
		} else {
			$l = $this->getCore()->getServer()->getDefaultLevel();
			$x = $c->getNested("$name.x");
			$y = $c->getNested("$name.y");
			$z = $c->getNested("$name.z");

			return new Position($x,$y,$z,$l);
		}
	}
	
	public function getPointGroup(string $name)
	{
		$c = $this->mappoints;

		if(!$c->exists($name)) {
			return -1;
		} else {
			return $c->getNested("$name.group");
		}
	}
	
	public function getPointsByGroup($group) : array
	{
		$c = $this->mappoints;

		$data = $c->getAll(true);
		$list = array();

		foreach($data as $name) {
			if($this->getPointGroup($name) == $group) array_push($list, $name);
		}

		return $list;
	}
	
	public function getAllPoints() : array
	{
		$c = $this->mappoints;
		
		$data = $c->getAll(true);
		$list = array();
		
		foreach($data as $name)
		{
			$pos = $this->getPointPosition($name);

			if ($pos == null) {
				continue;
			}

			$pos = $pos->asVector3();

			$list[] = array(
				"name" => $name,
				"position" => $pos
			);
		}
		
		return $list;
	}
	
	public function getNearPoints(Position $pos, int $rad = 7) : array
	{
		$c = $this->mappoints;

		$data = $c->getAll(true);
		$list = array();

		foreach($data as $name) {
			$x1 = $c->getNested("$name.x");
			$y1 = $c->getNested("$name.y");
			$z1 = $c->getNested("$name.z");

			$p_x = $pos->getX();
			$p_y = $pos->getY();
			$p_z = $pos->getZ();
			
			$x = $x1 - $p_x;
			$z = $z1 - $p_z;
			$y = $y1 - $p_y;

			$x = floor($x);
			$z = floor($z);
			$y = floor($y);

			if($x < $rad and $z < $rad and $x > $rad * -1 and $z > $rad * -1 and $y < $rad and $y > $rad * -1) {
				array_push($list,$name);
			}
		}

		return $list;
	}
}
?>
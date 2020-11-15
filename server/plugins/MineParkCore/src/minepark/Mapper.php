<?php
namespace minepark;

use minepark\mdc\dto\LocalMapPointDto;
use minepark\mdc\dto\MapPointDto;
use minepark\mdc\sources\MapSource;
use pocketmine\Player;
use pocketmine\level\Position;

class Mapper
{
	public $source;
	
	public function __construct()
	{
		$this->source = $this->getCore()->getMDC()->getSource("map");
	}

	private function getSource() : MapSource
	{
		return $this->source;
	}
	
	public const POINT_GROUP_GENERIC =  0;
	public const POINT_GROUP_REALTY = 1;
	public const POINT_GROUP_MARKETPLACE = 2;
	public const POINT_GROUP_SERVICE = 3;
	public const POINT_GROUP_STREAM = 4;
	public const POINT_GROUP_WORK1 = 5;
	public const POINT_GROUP_WORK2 = 6;
	public const POINT_GROUP_FASTFOOD = 7;

	public const POINT_NAME_JAIL = "КПЗ";

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function addPoint(Position $pos, string $name, int $group = 0)
	{
		$point = new MapPointDto();
		$point->name = $name;
		$point->level = $pos->getLevel()->getName();
		$point->x = $pos->getX();
		$point->y = $pos->getY();
		$point->z = $pos->getZ();
		$point->groupId = $group;

		$this->getSource()->setPoint($point);
	}
	
	public function removePoint(string $name) : bool
	{
		return $this->getSource()->deletePoint($name);
	}
	
	public function teleportPoint(Player $player, string $name)
	{
		$point = $this->getSource()->getPoint($name);

		if(is_null($point)) {
			$player->sendMessage("MapperInvalidPoint");
		} else {
			$level = $this->getCore()->getServer()->getLevelByName($point->level);
			$player->teleport(new Position($point->x, $point->y + 1, $point->z, $level));
		}
	}
	
	public function getPointPosition(string $name) : ?Position
	{
		$point = $this->getSource()->getPoint($name);

		if(is_null($point)) {
			return null;
		} else {
			$level = $this->getCore()->getServer()->getLevelByName($point->level);
			return new Position($point->x, $point->y + 1, $point->z, $level);
		}
	}
	
	public function getPointGroup(string $name) : ?int
	{
		return $this->getSource()->getPointGroup($name);
	}
	
	public function getPointsByGroup(int $group, bool $namesOnly = true) : array
	{
		$points = $this->getSource()->getPointsByGroup($group);
		return $namesOnly ? $this->getPointsNames($points) : $points;
	}
	
	public function getNearPoints(Position $pos, int $distance = 7, bool $namesOnly = true) : array
	{
		$dto = new LocalMapPointDto();
		$dto->level = $pos->getLevel()->getName();
		$dto->x = $pos->getX();
		$dto->y = $pos->getY();
		$dto->z = $pos->getZ();
		$dto->distance = $distance;

		$points = $this->getSource()->getNearPoints($dto);
		return $namesOnly ? $this->getPointsNames($points) : $points;
	}

	public function hasNearPointWithType(Position $pos, int $distance, int $group) : bool
	{
		$points = $this->getNearPoints($pos, $distance, false);


		foreach($points as $point) {
			if($point->groupId == $group) {
				return true;
			}
		}

		return false;
	}

	private function getPointsNames(array $points) : array
	{
		$names = [];

		foreach($points as $point) {
			array_push($names, $point->name);
		}

		return $names;
	}
}
?>
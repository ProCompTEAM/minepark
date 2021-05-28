<?php
namespace minepark\providers;

use pocketmine\level\Position;
use minepark\defaults\MapConstants;
use minepark\models\dtos\MapPointDto;
use minepark\providers\base\Provider;
use minepark\common\player\MineParkPlayer;
use minepark\models\dtos\LocalMapPointDto;
use minepark\Providers;
use minepark\providers\data\MapDataProvider;

class MapProvider extends Provider
{
    private const DEFAULT_NEAR_POINTS_DISTANCE = 7;

    private MapDataProvider $dataProvider;

    public function __construct()
    {
        $this->dataProvider = Providers::getMapDataProvider();
    }

    public function addPoint(Position $position, string $pointName, int $group = MapConstants::POINT_GROUP_GENERIC)
    {
        $pointDto = $this->getMapPointDto($pointName, $group, $position);

        $this->dataProvider->setPoint($pointDto);
    }

    public function removePoint(string $pointName) : bool
    {
        return $this->dataProvider->deletePoint($pointName);
    }

    public function getPointPosition(string $pointName) : ?Position
    {
        $pointData = $this->dataProvider->getPoint($pointName);

        if (!isset($pointData)) {
            return null;
        }

        $level = $this->getServer()->getLevelByName($pointData->level);

        return new Position($pointData->x, $pointData->y, $pointData->z, $level);
    }

    public function getPointGroup(string $pointName) : ?int
    {
        return $this->dataProvider->getPointGroup($pointName);
    }

    public function getPointsByGroup(int $group, bool $namesOnly = true) : array
    {
        $points = $this->dataProvider->getPointsByGroup($group);

        return $namesOnly ? $this->getPointsNames($points) : $points;
    }

    public function getNearPoints(Position $position, int $distance = self::DEFAULT_NEAR_POINTS_DISTANCE, bool $namesOnly = true)
    {
        $localMapPointDto = $this->getLocalMapPointDto($position, $distance);

        $points = $this->dataProvider->getNearPoints($localMapPointDto);

        return $namesOnly ? $this->getPointsNames($points) : $points;
    }

    public function hasNearPointWithType(Position $pos, int $distance, int $group) : bool
    {
        $points = $this->getNearPoints($pos, $distance, false);

        foreach ($points as $point) {
            if ($point->groupId === $group) {
                return true;
            }
        }

        return false;
    }

    public function teleportPoint(MineParkPlayer $player, string $pointName)
    {
        $pointPosition = $this->getPointPosition($pointName);

        if (!isset($pointPosition)) {
            return $player->sendMessage("MapperInvalidPoint");
        }

        $player->teleport($pointPosition);
    }

    private function getPointsNames(array $points) : array
    {
        $names = [];

        foreach ($points as $point) {
            $names[] = $point->name;
        }

        return $names;
    }

    private function getLocalMapPointDto(Position $position, int $distance) : LocalMapPointDto
    {
        $localMapPointDto = new LocalMapPointDto;

        $localMapPointDto->level = $position->getLevel()->getName();
        $localMapPointDto->x = $position->getX();
        $localMapPointDto->y = $position->getY();
        $localMapPointDto->z = $position->getZ();
        $localMapPointDto->distance = $distance;

        return $localMapPointDto;
    }

    private function getMapPointDto(string $pointName, int $group, Position $position) : MapPointDto
    {
        $dto = new MapPointDto;

        $dto->name = $pointName;
        $dto->level = $position->getLevel()->getName();
        $dto->x = $position->getX();
        $dto->y = $position->getY();
        $dto->z = $position->getZ();
        $dto->groupId = $group;

        return $dto;
    }
}
<?php
namespace minepark\providers\data;

use minepark\models\dtos\MapPointDto;
use minepark\models\dtos\LocalMapPointDto;
use minepark\providers\base\DataProvider;

class MapDataProvider extends DataProvider
{
    public const ROUTE = "map";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function getPoint(string $name) : ?MapPointDto
    {
        $requestResult = $this->createRequest("get-point", $name);

        return $requestResult ? $this->createDto($requestResult) : null;
    }

    public function getPointGroup(string $name) : ?int
    {
        return (int) $this->createRequest("get-point-group", $name);
    }

    public function getPointsByGroup(int $groupId) : array
    {
        $requestResult = $this->createRequest("get-points-by-group", $groupId);

        return $this->createListDto($requestResult);
    }

    public function getNearPoints(LocalMapPointDto $dto) : array
    {
        $requestResult = $this->createRequest("get-near-points", $dto);

        return $this->createListDto($requestResult);
    }

    public function setPoint(MapPointDto $dto)
    {
        $this->createRequest("set-point", $dto);
    }

    public function deletePoint(string $name) : bool
    {
        return (bool) $this->createRequest("delete-point", $name);
    }

    protected function createDto(array $data) : MapPointDto
    {
        $dto = new MapPointDto();
        $dto->set($data);
        return $dto;
    }

    protected function createListDto(array $data) : array
    {
        foreach($data as $index => $value) {
            $data[$index] = new MapPointDto();
            $data[$index]->set($value);
        }
        return $data;
    }
}
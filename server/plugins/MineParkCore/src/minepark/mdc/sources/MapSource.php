<?php
namespace minepark\mdc\sources;

use minepark\mdc\dto\MapPointDto;
use minepark\mdc\dto\LocalMapPointDto;

class MapSource extends RemoteSource
{
    public function getName() : string
    {
        return "map";
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
?>
<?php
namespace minepark\mdc\dto;

class MapPointDto extends BaseDto
{
    public string $name;

    public string $level;

    public float $x;

    public float $y;

    public float $z;

    public int $groupId;

    public string $createdDate;
}
?>
<?php
namespace minepark\models\dtos;

class LocalMapPointDto extends BaseDto
{
    public string $level;

    public float $x;

    public float $y;

    public float $z;

    public int $distance;
}
?>
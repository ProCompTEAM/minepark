<?php
namespace minepark\mdc\dtos;

class LocalMapPointDto extends BaseDto
{
    public string $level;

    public float $x;

    public float $y;

    public float $z;

    public int $distance;
}
?>
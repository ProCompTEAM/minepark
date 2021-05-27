<?php
namespace minepark\models\dtos;

class LocalFloatingTextDto extends BaseDto
{
    public string $text;

    public string $level;

    public float $x;

    public float $y;

    public float $z;
}
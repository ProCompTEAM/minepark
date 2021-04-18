<?php
namespace minepark\models\dtos;

class FloatingTextDto extends BaseDto
{
    public string $text;

    public string $level;

    public int $x;

    public int $y;

    public int $z;

    public string $createdDate;
}
?>
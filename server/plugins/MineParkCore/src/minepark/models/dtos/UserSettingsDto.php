<?php
namespace minepark\models\dtos;

class UserSettingsDto extends BaseDto
{
    public int $id;

    public string $name;

    public ?string $licenses;

    public ?string $attributes;
    
    public int $organisation;

    public ?string $world;

    public float $x;

    public float $y;

    public float $z;
}
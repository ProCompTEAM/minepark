<?php
namespace minepark\models\dtos;

class UserDto extends BaseDto
{
    public int $id;

    public string $name;

    public string $fullName;

    public ?string $group;

    public ?string $licenses;

    public ?string $attributes;

    public ?string $people;

    public ?string $tag;

    public int $organisation;

    public int $bonus;

    public int $minutesPlayed;

    public int $phoneNumber;

    public bool $vip;

    public bool $administrator;

    public bool $builder;

    public bool $realtor;

    public string $lastVersionNotified;

    public string $joinedDate;

    public string $leftDate;

    public string $createdDate;

    public string $updatedDate;
}
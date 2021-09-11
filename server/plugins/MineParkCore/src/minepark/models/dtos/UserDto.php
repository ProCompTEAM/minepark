<?php
namespace minepark\models\dtos;

class UserDto extends BaseDto
{
    public int $id;

    public string $name;

    public string $fullName;

    public ?string $email;

    public ?string $group;

    public ?string $people;

    public ?string $tag;

    public int $bonus;

    public int $minutesPlayed;

    public int $phoneNumber;

    public bool $vip;

    public bool $administrator;

    public bool $builder;

    public bool $realtor;

    public ?PlayerBanDto $ban;

    public string $joinedDate;

    public string $leftDate;

    public string $createdDate;

    public string $updatedDate;
}
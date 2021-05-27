<?php
namespace minepark\models\player;

class BossBarSession
{
    public ?string $title;

    public ?int $percents;

    public int $fakeEntityId;

    public bool $loaded;
}
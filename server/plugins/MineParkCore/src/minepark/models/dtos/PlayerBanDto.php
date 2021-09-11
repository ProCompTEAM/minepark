<?php

namespace minepark\models\dtos;

class PlayerBanDto extends BaseDto
{
    public string $userName;

    public string $issuer;

    public string $end;

    public string $reason;
}
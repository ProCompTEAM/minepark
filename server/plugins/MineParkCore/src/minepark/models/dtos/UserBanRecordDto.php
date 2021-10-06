<?php

namespace minepark\models\dtos;

class UserBanRecordDto extends BaseDto
{
    public string $userName;

    public string $issuerName;

    public string $releaseDate;

    public string $reason;
}
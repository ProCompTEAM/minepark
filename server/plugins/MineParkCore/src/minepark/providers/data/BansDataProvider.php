<?php

namespace minepark\providers\data;

use DateTime;
use minepark\models\dtos\PlayerBanDto;
use minepark\providers\base\DataProvider;
use minepark\utils\DateTimeUtility;
use pocketmine\player\Player;

class BansDataProvider extends DataProvider
{
    public const ROUTE = "bans";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function getPlayerBanInfo(string $userName) : ?PlayerBanDto
    {
        $requestResult = $this->createRequest("get-player-ban-info", $userName);

        return $requestResult ? $this->createDto($requestResult) : null;
    }

    public function banPlayer(string $userName, string $issuer, DateTime $dateTime, string $reason) : bool
    {
        $dto = new PlayerBanDto;

        $dto->userName = $userName;
        $dto->issuer = $issuer;
        $dto->end = DateTimeUtility::parseDateTimeToString($dateTime);
        $dto->reason = $reason;

        return (bool) $this->createRequest("ban-player", $dto);
    }

    public function unbanPlayer(string $userName) : bool
    {
        return (bool) $this->createRequest("unban-player", $userName);
    }

    public function isBanned(string $userName) : bool
    {
        return (bool) $this->createRequest("is-banned", $userName);
    }

    protected function createDto(array $data) : PlayerBanDto
    {
        $dto = new PlayerBanDto;
        $dto->set($data);
        return $dto;
    }
}
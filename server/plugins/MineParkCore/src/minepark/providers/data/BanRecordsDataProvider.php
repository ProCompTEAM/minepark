<?php

namespace minepark\providers\data;

use DateTime;
use minepark\models\dtos\UserBanRecordDto;
use minepark\providers\base\DataProvider;
use minepark\utils\DateTimeUtility;
use pocketmine\player\Player;

class BanRecordsDataProvider extends DataProvider
{
    public const ROUTE = "bans";

    public function getRoute() : string
    {
        return self::ROUTE;
    }

    public function getUserBanRecord(string $userName) : ?UserBanRecordDto
    {
        $requestResult = $this->createRequest("get-user-ban-record", $userName);

        return $requestResult ? $this->createDto($requestResult) : null;
    }

    public function banUser(string $userName, string $issuerName, DateTime $releaseDate, string $reason) : bool
    {
        $dto = new UserBanRecordDto;

        $dto->userName = $userName;
        $dto->issuerName = $issuerName;
        $dto->releaseDate = DateTimeUtility::parseDateTimeToString($releaseDate);
        $dto->reason = $reason;

        return (bool) $this->createRequest("ban-user", $dto);
    }

    public function pardonUser(string $userName) : bool
    {
        return (bool) $this->createRequest("pardon-user", $userName);
    }

    public function isBanned(string $userName) : bool
    {
        return (bool) $this->createRequest("is-banned", $userName);
    }

    protected function createDto(array $data) : UserBanRecordDto
    {
        $dto = new UserBanRecordDto;
        $dto->set($data);
        return $dto;
    }
}
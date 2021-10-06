<?php

namespace minepark\components\administrative;

use DateTime;
use minepark\common\player\MineParkPlayer;
use minepark\components\base\Component;
use minepark\defaults\ComponentAttributes;
use minepark\models\dtos\UserBanRecordDto;
use minepark\Providers;
use minepark\providers\data\BanRecordsDataProvider;
use minepark\providers\data\UsersDataProvider;
use minepark\utils\DateTimeUtility;

class BanSystem extends Component
{
    private BanRecordsDataProvider $banRecordsDataProvider;

    private UsersDataProvider $usersDataProvider;

    public function initialize()
    {
        $this->banRecordsDataProvider = Providers::getBanRecordsDataProvider();

        $this->usersDataProvider = Providers::getUsersDataProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function getOnlineUserBanInfo(MineParkPlayer $player)
    {
        return $this->banRecordsDataProvider->getUserBanRecord($player->getName());
    }

    public function getOfflineUserBanInfo(string $userName) : ?UserBanRecordDto
    {
        return $this->banRecordsDataProvider->getUserBanRecord($userName);
    }

    public function banOnlineUser(MineParkPlayer $player, string $issuerName, DateTime $releaseDate, string $reason)
    {
        $userName = $player->getName();

        $status = $this->banRecordsDataProvider->banUser($userName, $issuerName, $releaseDate, $reason);

        $releaseDateString = DateTimeUtility::parseDateTimeToString($releaseDate);

        $this->kickPlayer($player, $issuerName, $releaseDateString, $reason);

        $this->broadcastBanMessage($userName, $issuerName, $releaseDateString, $reason);
    }

    public function banOfflineUser(string $userName, string $issuerName, DateTime $releaseDate, string $reason) : bool
    {
        $status = $this->banRecordsDataProvider->banUser($userName, $issuerName, $releaseDate, $reason);

        if($status) {
            $releaseDateString = DateTimeUtility::parseDateTimeToString($releaseDate);

            $this->broadcastBanMessage($userName, $issuerName, $releaseDateString, $reason);
        }

        return $status;
    }

    public function pardonUser(string $userName, ?string $issuer = null) : bool
    {
        $status = $this->banRecordsDataProvider->pardonUser($userName);

        if($status) {
            $this->broadcastUnbanMessage($userName, $issuer);
        }

        return $status;
    }

    public function isBanned(string $userName) : bool
    {
        return $this->banRecordsDataProvider->isBanned($userName);
    }

    private function broadcastBanMessage(string $target, string $issuer, string $dateTime, string $reason)
    {
        $message = "§c[§eБАНЫ§c] §eИгрок§b $target §eбыл заблокирован администратором§b $issuer §eпо причине§b $reason. §eДата окончания блокировки:§b $dateTime";

        $this->getServer()->broadcastMessage($message);
    }

    private function broadcastUnbanMessage(string $target, ?string $issuer = null)
    {
        $message = "§c[§eБАНЫ§c] §eИгрок§b $target §eбыл разблокирован";

        if(!is_null($issuer)) {
            $message = $message . " администратором§b $issuer";
        }

        $this->getServer()->broadcastMessage($message);
    }

    private function kickPlayer(MineParkPlayer $target, string $issuer, string $dateTime, string $reason)
    {
        $target->kick("§eВы были заблокированы администратором§b $issuer §eпо причине§b $reason.\n§eДата окончания блокировки -§b $dateTime");
    }
}
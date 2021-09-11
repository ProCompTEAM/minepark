<?php

namespace minepark\components\administrative;

use DateTime;
use minepark\common\player\MineParkPlayer;
use minepark\components\base\Component;
use minepark\defaults\ComponentAttributes;
use minepark\models\dtos\PlayerBanDto;
use minepark\Providers;
use minepark\providers\data\BansDataProvider;
use minepark\providers\data\UsersDataProvider;
use minepark\utils\DateTimeUtility;

class Bans extends Component
{
    private BansDataProvider $bansDataProvider;

    private UsersDataProvider $usersDataProvider;

    public function initialize()
    {
        $this->bansDataProvider = Providers::getBansDataProvider();

        $this->usersDataProvider = Providers::getUsersDataProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function getPlayerBanInfo(MineParkPlayer|string $player) : ?PlayerBanDto
    {
        if(is_string($player)) {
            if(!$this->playerExists($player)) {
                return null;
            }
        } else {
            $player = $player->getName();
        }

        return $this->bansDataProvider->getPlayerBanInfo($player);
    }

    public function banPlayer(MineParkPlayer|string $player, string $issuer, DateTime $dateTime, string $reason) : bool
    {
        if(is_string($player)) {
            if(!$this->playerExists($player)) {
                return false;
            }
        } else {
            $player = $player->getName();
        }

        $status = $this->bansDataProvider->banPlayer($player, $issuer, $dateTime, $reason);

        if($status) {
            $dateTime = DateTimeUtility::parseDateTimeToString($dateTime);

            $this->tryToKickPlayer($player, $issuer, $dateTime, $reason);

            $this->broadcastBanMessage($player, $issuer, $dateTime, $reason);
        }

        return $status;
    }

    public function unbanPlayer(string $userName, ?string $issuer = null) : bool
    {
        $status = $this->bansDataProvider->unbanPlayer($userName);

        if($status) {
            $this->broadcastUnbanMessage($userName, $issuer);
        }

        return $status;
    }

    public function isBanned(MineParkPlayer|string $player) : bool
    {
        if(!is_string($player)) {
            $player = $player->getName();
        }

        return $this->bansDataProvider->isBanned($player);
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

    private function playerExists(string $userName)
    {
        return $this->usersDataProvider->isUserExist($userName);
    }

    private function tryToKickPlayer(string $target, string $issuer, string $dateTime, string $reason)
    {
        $player = $this->getServer()->getPlayerExact($target);

        if(!is_null($player)) {
            $player->kick("§eВы были заблокированы администратором§b $issuer §eпо причине§b $reason.\n§eДата окончания блокировки -§b $dateTime");
        }
    }
}
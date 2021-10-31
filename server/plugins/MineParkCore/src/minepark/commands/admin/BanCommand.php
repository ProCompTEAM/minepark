<?php

namespace minepark\commands\admin;

use DateInterval;
use DateTime;
use jojoe77777\FormAPI\CustomForm;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\administrative\BanSystem;
use minepark\defaults\Permissions;
use minepark\Providers;
use minepark\providers\data\UsersDataProvider;
use pocketmine\event\Event;

class BanCommand extends Command
{
    private const COMMAND_NAME = "ban";

    private const MONTHS_PATTERN_SUFFIX = "M";

    private const DAYS_PATTERN_SUFFIX = "D";

    private const TIMESTAMP_START_PATTERN = "T";

    private const HOURS_PATTERN_SUFFIX = "H";

    private const MINIMAL_NAME_LENGTH = 1;

    private const MINIMAL_REASON_LENGTH = 1;

    private const MAXIMAL_MONTHS = 12;

    private const MAXIMAL_DAYS = 30;

    private const MAXIMAL_HOURS = 24;

    private UsersDataProvider $usersDataProvider;

    private BanSystem $banSystem;

    public function __construct()
    {
        $this->usersDataProvider = Providers::getUsersDataProvider();

        $this->banSystem = Components::getComponent(BanSystem::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ADMINISTRATOR,
            Permissions::OPERATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $this->sendBanForm($player);
    }

    private function sendBanForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerBanForm"]);

        $form->setTitle("Блокировка игроков");

        $form->addInput("Имя игрока");
        $form->addInput("Причина бана");
        $form->addSlider("Кол-во месяцев", 0, self::MAXIMAL_MONTHS);
        $form->addSlider("Кол-во дней", 0, self::MAXIMAL_DAYS);
        $form->addSlider("Кол-во часов", 0, self::MAXIMAL_HOURS);

        $player->sendForm($form);
    }

    public function answerBanForm(MineParkPlayer $player, ?array $inputData = null)
    {
        if(is_null($inputData)) {
            return;
        }

        if(!isset($inputData[4])) {
            $player->sendMessage("§eПроизошла ошибка. Попробуйте позже.");
            return;
        }

        $userName = $inputData[0];
        $reason = $inputData[1];
        $months = $inputData[2];
        $days = $inputData[3];
        $hours = $inputData[4];

        if(!$this->checkInputData($player, $userName, $reason, $months, $days, $hours)) {
            return;
        }

        $this->processBan($player, $userName, $reason, $months, $days, $hours);
    }

    private function checkInputData(MineParkPlayer $player, string $userName, string $reason, int $months, int $days, int $hours) : bool
    {
        if(!is_numeric($months) or !is_numeric($days) or !is_numeric($hours)) {
            $player->sendMessage("§eПроизошла ошибка. Попробуйте позже");
            return false;
        }

        if($months === 0 and $days === 0 and $hours === 0) {
            $player->sendMessage("§cВы не указали время бана!");
            return false;
        }

        if($months > self::MAXIMAL_MONTHS or $days > self::MAXIMAL_DAYS or $hours > self::MAXIMAL_HOURS) {
            $player->sendMessage("§eПроизошла ошибка. Попробуйте позже");
            return false;
        }

        if(strlen($userName) < self::MINIMAL_NAME_LENGTH) {
            $player->sendMessage("§cВы не указали ник игрока, которого желаете забанить!");
            return false;
        }

        if(strlen($reason) < self::MINIMAL_REASON_LENGTH) {
            $player->sendMessage("§cВы не указали причину бана!");
            return false;
        }

        return true;
    }

    private function processBan(MineParkPlayer $issuer, string $targetName, string $reason, int $months, int $days, int $hours)
    {
        $target = $this->getServer()->getPlayerByPrefix($targetName);

        if(!is_null($target)) {
            $this->banOnlinePlayer($issuer, $target, $reason, $months, $days, $hours);
        } else {
            $this->banOfflinePlayer($issuer, $targetName, $reason, $months, $days, $hours);
        }
    }

    private function banOnlinePlayer(MineParkPlayer $issuer, MineParkPlayer $target, string $reason, int $months, int $days, int $hours)
    {
        $releaseDate = $this->createDateTime($months, $days, $hours);

        $this->banSystem->banOnlineUser($target, $issuer->getName(), $releaseDate, $reason);

        $issuer->sendMessage("§eИгрок §b" . $target->getName() . "§e был успешно заблокирован!");
    }

    private function banOfflinePlayer(MineParkPlayer $issuer, string $targetName, string $reason, int $months, int $days, int $hours)
    {
        if(!$this->usersDataProvider->isUserExist($targetName)) {
            $issuer->sendMessage("§eИгрока§b $targetName §eне существует :(");
            return;
        }

        $releaseDate = $this->createDateTime($months, $days, $hours);

        $banStatus = $this->banSystem->banOfflineUser($targetName, $issuer->getName(), $releaseDate, $reason);

        if($banStatus) {
            $issuer->sendMessage("§eИгрок§b $targetName §eбыл успешно заблокирован!");
        } else {
            $issuer->sendMessage("§eИгрок§b $targetName §eуже в бане!");
        }
    }

    private function createDateTime(int $months, int $days, int $hours) : DateTime
    {
        $dateTime = new DateTime;
        $dateTime->add($this->createDateInterval($months, $days, $hours));

        return $dateTime;
    }

    private function createDateInterval(int $months, int $days, int $hours) : DateInterval
    {
        $dateIntervalPattern = "P";

        if($months > 0) {
            $dateIntervalPattern = $dateIntervalPattern . $months . self::MONTHS_PATTERN_SUFFIX;
        }

        if($days > 0) {
            $dateIntervalPattern = $dateIntervalPattern . $days . self::DAYS_PATTERN_SUFFIX;
        }

        if($hours > 0) {
            $dateIntervalPattern = $dateIntervalPattern . self::TIMESTAMP_START_PATTERN . $hours . self::HOURS_PATTERN_SUFFIX;
        }

        return new DateInterval($dateIntervalPattern);
    }
}
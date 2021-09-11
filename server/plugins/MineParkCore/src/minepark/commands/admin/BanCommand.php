<?php

namespace minepark\commands\admin;

use DateInterval;
use DateTime;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\administrative\Bans;
use minepark\defaults\Permissions;
use pocketmine\event\Event;

class BanCommand extends Command
{
    private const COMMAND_NAME = "ban";

    private Bans $bans;

    public function __construct()
    {
        $this->bans = Components::getComponent(Bans::class);
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
        if(!self::argumentsMin(2, $args)) {
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $this->tryParsingArguments($player, $args);
    }

    private function tryParsingArguments(MineParkPlayer $player, array $arguments)
    {
        $target = $arguments[0];

        $arguments = array_slice($arguments, 1);

        $months = 0;
        $days = 0;
        $hours = 0;

        for($i = 0; $i < count($arguments); $i++) {
            $argument = $arguments[$i];

            $suffix = substr($argument, -1);
            $number = substr($argument, 0, strlen($argument) - 1);

            if(!is_numeric($number)) {
                break;
            }

            $number = (int) $number;

            if($suffix === "m") {
                $months += $number;
            } else if($suffix === "d") {
                $days += $number;
            } else if($suffix === "h") {
                $hours += $number;
            } else {
                break;
            }
        }

        if($i === 0) {
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $dateTime = $this->createDateTime($months, $days, $hours);

        $arguments = array_slice($arguments, $i);

        if(!isset($arguments[0])) {
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $reason = implode(self::ARGUMENTS_SEPERATOR, $arguments);

        $this->tryBanningPlayer($player, $target, $reason, $dateTime);
    }

    private function tryBanningPlayer(MineParkPlayer $player, string $target, string $reason, DateTime $dateTime)
    {
        $result = $this->bans->banPlayer($target, $player->getName(), $dateTime, $reason);

        if($result) {
            $player->sendMessage("§eИгрок успешно заблокирован!");
        } else {
            $player->sendMessage("§eВозможно, игрок§b $target §eне существует или уже заблокирован");
        }
    }

    private function sendInvalidCommandUsage(MineParkPlayer $player)
    {
        $player->sendMessage("§eНеверное использование данной команды. Формат: §b/ban (имя игрока) (время) (причина)");
    }

    private function createDateTime(int $months, int $days, int $hours) : DateTime
    {
        $timePattern = "P";

        if($months !== 0) {
            $timePattern = $timePattern . $months . "M";
        }

        if($days !== 0) {
            $timePattern = $timePattern . $days . "D";
        }

        if($hours !== 0) {
            $timePattern = $timePattern . "T";

            $timePattern = $timePattern . $hours . "H";
        }

        $dateInterval = new DateInterval($timePattern);

        return (new DateTime)->add($dateInterval);
    }
}
<?php

namespace minepark\commands\admin;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\administrative\Bans;
use minepark\defaults\Permissions;
use pocketmine\event\Event;

class UnbanCommand extends Command
{
    public const COMMAND_NAME = "unban";

    public const COMMAND_ALIAS = "pardon";

    private Bans $bans;

    public function __construct()
    {
        $this->bans = Components::getComponent(Bans::class);
    }

    public function getCommand() : array
    {
        return [
            self::COMMAND_NAME,
            self::COMMAND_ALIAS
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $this->sendInvalidCommandUsage($player);
            return;
        }

        $status = $this->bans->unbanPlayer($args[0], $player->getName());

        if($status) {
            $player->sendMessage("§eИгрок §b" . $args[0] . " §eразблокирован!");
        } else {
            $player->sendMessage("§eВозможно, данного игрока не существует или он попросту не заблокирован");
        }
    }

    private function sendInvalidCommandUsage(MineParkPlayer $player)
    {
        $player->sendMessage("§eНеверное использование данной команды. Формат: §b/unban (имя игрока)");
    }
}
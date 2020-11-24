<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permissions;

class MoneyCommand extends Command
{
    public const CURRENT_COMMAND = "money";
    public const CURRENT_COMMAND_ALIAS = "balance";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND,
            self::CURRENT_COMMAND_ALIAS
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $money = $this->getCore()->getBank()->getPlayerMoney($player);
        $player->sendLocalizedMessage("{CommandMoneyPart1}". $money . "{CommandMoneyPart2}");
    }
}
?>
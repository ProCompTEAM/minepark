<?php
namespace minepark\commands;

use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

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

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $cash = $this->getCore()->getBank()->getCash($player);
        $debit = $this->getCore()->getBank()->getDebit($player);
        $credit = $this->getCore()->getBank()->getCredit($player);

        $player->sendMessage("§2→ Наличные§e $cash §3рублей.");
        $player->sendMessage("§3→ На карте§e $debit §3рублей.");
        $player->sendMessage("§4→ В кредит§e $credit §3рублей.");
    }
}
?>
<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\providers\BankingProvider;

class MoneyCommand extends Command
{
    public const CURRENT_COMMAND = "money";
    public const CURRENT_COMMAND_ALIAS = "balance";

    private BankingProvider $bankingProvider;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();
    }

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
        $cash = $this->bankingProvider->getCash($player);
        $debit = $this->bankingProvider->getDebit($player);
        $credit = $this->bankingProvider->getCredit($player);

        $player->sendMessage("§2→ Наличные§e $cash §3рублей.");
        $player->sendMessage("§3→ На карте§e $debit §3рублей.");
        $player->sendMessage("§4→ В кредит§e $credit §3рублей.");
    }
}
?>
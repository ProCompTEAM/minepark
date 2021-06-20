<?php
namespace minepark\commands;

use minepark\Components;
use minepark\components\map\ATM;
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

    private ATM $atm;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->atm = Components::getComponent(ATM::class);
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
        $this->atm->sendMoneyInfo($player);
    }
}
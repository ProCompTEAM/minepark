<?php
namespace minepark\commands\economy;

use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\providers\BankingProvider;

class MoneyGiftCommand extends Command
{
    public const CURRENT_COMMAND = "moneygift";

    private const MAX_GIFT_SUM = 1000000;

    private BankingProvider $bankingProvider;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();
    }

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::OPERATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(!self::argumentsMin(2, $args) or !is_numeric($args[1])) {
            $player->sendMessage("CommandMoneyGiftUsage");
            return;
        }

        $targetPlayerName = $args[0];
        $sum = $args[1];

        $player->sendSound(Sounds::ROLEPLAY);

        if($sum > self::MAX_GIFT_SUM) {
            $player->sendMessage("CommandMoneyGiftMax");
            return;
        }

        $targetPlayer = $this->getServer()->getPlayerByPrefix($targetPlayerName);
        if(!is_null($targetPlayer)) {
            $this->transferMoney($player, $targetPlayer, $sum);
            $this->notifyOperators($player, $targetPlayer, $sum);
        } else {
            $player->sendMessage("CommandMoneyGiftNoPlayer");
        }
    }

    private function transferMoney(MineParkPlayer $operator, MineParkPlayer $targetPlayer, float $sum)
    {
        //transfer sum through operator for mdc audit log
        $this->bankingProvider->giveDebit($operator, $sum);
        $this->bankingProvider->transferDebit($operator->getName(), $targetPlayer->getName(), $sum);
    }

    private function notifyOperators(MineParkPlayer $operator, MineParkPlayer $targetPlayer, float $sum)
    {
        $targetPlayer->sendLocalizedMessage("{CommandMoneyGiftMessage1} $sum \n{CommandMoneyGiftMessage2}");

        $operatorName = $operator->getName();
        $targetPlayerName = $targetPlayer->getName();

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            if($player->isOperator()) {
                $player->sendLocalizedMessage("{CommandMoneyGiftNotification} [$sum] $operatorName -> $targetPlayerName");
            }
        }
    }
}
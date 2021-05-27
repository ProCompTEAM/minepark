<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\GameChat;
use minepark\providers\BankingProvider;

class PayCommand extends Command
{
    public const CURRENT_COMMAND = "pay";

    public const DISTANCE = 6;

    private BankingProvider $bankingProvider;

    private GameChat $gameChat;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->gameChat = Components::getComponent(GameChat::class);
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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::CHAT_SOUND);

        if(self::argumentsNo($args) or !is_numeric($args[0])) {
            $player->sendMessage("CommandPayUse");
            return;
        }

        $players = $this->getCore()->getRegionPlayers($player, self::DISTANCE);

        if(count($players) > 2) {
            $player->sendMessage("CommandPayCountPlayer");
            return;
        }

        $this->gameChat->sendLocalMessage($player, "{CommandPayTake}", "§d : ", self::DISTANCE);
        foreach($players as $p) {
            if($p === $player) {
                continue;
            } else {
                if($this->bankingProvider->reduceCash($player, $args[0])) {
                    $this->gameChat->sendLocalMessage($player, "{CommandPayPay}", "§d", self::DISTANCE);
                    $this->bankingProvider->giveCash($p, $args[0]);
                } else {
                    $player->sendMessage("CommandPayNoMoney");
                }
            }
        }
        
        $this->gameChat->sendLocalMessage($player, "{CommandPayPut}", "§d", self::DISTANCE);
        
        $event->setCancelled();
    }
}
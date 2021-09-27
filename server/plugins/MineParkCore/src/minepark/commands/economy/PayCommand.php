<?php
namespace minepark\commands\economy;

use minepark\Providers;
use pocketmine\event\Event;

use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\chat\Chat;
use minepark\providers\BankingProvider;

class PayCommand extends Command
{
    public const CURRENT_COMMAND = "pay";

    public const DISTANCE = 6;

    private BankingProvider $bankingProvider;

    private Chat $chat;

    public function __construct()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->chat = Components::getComponent(Chat::class);
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

        $players = $this->getCore()->getRegionPlayers($player->getPosition(), self::DISTANCE);

        if(count($players) > 2) {
            $player->sendMessage("CommandPayCountPlayer");
            return;
        }

        $this->chat->sendLocalMessage($player, "{CommandPayTake}", "§d : ", self::DISTANCE);
        foreach($players as $p) {
            if($p === $player) {
                continue;
            } else {
                if($this->bankingProvider->reduceCash($player, $args[0])) {
                    $this->chat->sendLocalMessage($player, "{CommandPayPay}", "§d", self::DISTANCE);
                    $this->bankingProvider->giveCash($p, $args[0]);
                } else {
                    $player->sendMessage("CommandPayNoMoney");
                }
            }
        }
        
        $this->chat->sendLocalMessage($player, "{CommandPayPut}", "§d", self::DISTANCE);
        
        $event->cancel();
    }
}
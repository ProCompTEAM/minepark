<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permissions;
use minepark\Sounds;

class PayCommand extends Command
{
    public const CURRENT_COMMAND = "pay";

    public const DISTANCE = 6;

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

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        $player->sendSound(Sounds::CHAT_SOUND);

        if(self::argumentsNo($args) or !is_numeric($args[0])) {
            $player->sendMessage("CommandPayUse");
            return;
        }

        $players = $this->getCore()->getApi()->getRegionPlayers($player, self::DISTANCE);

        if(count($players) > 2) {
            $player->sendMessage("CommandPayCountPlayer");
            return;
        }

        $this->getCore()->getChatter()->send($player, "{CommandPayTake}", "§d : ", self::DISTANCE);
        foreach($players as $p) {
            if($p === $player) {
                continue;
            } else {
                if($this->getCore()->getBank()->takePlayerMoney($player, $args[0])) {
                    $this->getCore()->getChatter()->send($player, "{CommandPayPay}", "§d", self::DISTANCE);
                    $this->getCore()->getBank()->givePlayerMoney($p, $args[0]);
                } else {
                    $player->sendMessage("CommandPayNoMoney");
                }
            }
        }
        
        $this->getCore()->getChatter()->send($player, "{CommandPayPut}", "§d", self::DISTANCE);
        
        $event->setCancelled();
    }
}
?>
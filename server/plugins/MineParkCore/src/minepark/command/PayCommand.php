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
            $player->sendMessage("§сНедопустимая операция, /pay <сумма>");
            return;
        }

        $players = $this->getCore()->getApi()->getRegionPlayers($player, self::DISTANCE);

        if(count($players) > 2) {
            $player->sendMessage("§сРядом есть посторонние лица. Операция проводится в условиях приватности!");
            return;
        }

        $this->getCore()->getChatter()->send($player, "§8(§dв руках бумажник§8)", "§d : ", self::DISTANCE);
        foreach($players as $p) {
            if($p === $player) {
                continue;
            } else {
                if($this->getCore()->getBank()->takePlayerMoney($player, $args[0])) {
                    $this->getCore()->getChatter()->send($player, "передал(а) деньги человеку напротив", "§d", self::DISTANCE);
                    $this->getCore()->getBank()->givePlayerMoney($p, $args[0]);
                } else {
                    $player->sendMessage("§сВам нехватило денег для передачи");
                }
            }
        }
        
        $this->getCore()->getChatter()->send($player, "положил(а) бумажник в карман", "§d", self::DISTANCE);
        
        $event->setCancelled();
    }
}
?>
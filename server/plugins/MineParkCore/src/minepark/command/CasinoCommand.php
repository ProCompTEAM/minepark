<?php
namespace minepark\command;

use pocketmine\Player;
use pocketmine\event\Event;
use pocketmine\level\Position;

use minepark\Permission;

class CasinoCommand extends Command
{
    public const CURRENT_COMMAND = "casino";

    public const CASINO_POINT_NAME = "Казино";

    public const CASINO_DISTANCE = 10;

    public const CASINO_MIN_SUM = 2000;
    public const CASINO_MAX_SUM = 500000;
    public const CASINO_CHANCE = 3;

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permission::ANYBODY
        ];
    }

    public function execute(Player $player, array $args = array(), Event $event = null)
    {
        if(!$this->checkState($player, $args)) {
            return;
        }

        if($this->getCore()->getBank()->takePlayerMoney($player, $args[0], false))
        {
            if(mt_rand(1, self::CASINO_CHANCE) == 1) {
                $this->getCore()->getBank()->givePlayerMoney($player, $args[0] * 2);
                $player->sendMessage("§aПоздравляем с выйгрышем! Теперь эти деньги Ваши!");
            } else { 
                $player->sendMessage("§eПройгрыш! Не печальтесь, попробуйте снова!");
            }
        } else {
          $player->sendMessage("§сСредства отсутствуют на счете!");  
        } 

        $event->setCancelled();
    }

    private function getCasinoPoint(Position $position) : ?string
    {
        $plist = $this->getCore()->getMapper()->getNearPoints($position, self::CASINO_DISTANCE); 
        
        foreach($plist as $point)
        {
            if($point == self::CASINO_POINT_NAME) {
                return $point;
            }
        }

        return null;
    }

    private function checkState(Player $player, array $args) : bool
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("§6Шанс выйгрыша 50%! Использование: /casino <сумма>");
            return false;
        }

        if($this->getCasinoPoint($player->getPosition()) == null) {
            $player->sendMessage("§cВам необходимо находиться около игровых автоматов!");
            return false;
        }

        if($args[0] < self::CASINO_MIN_SUM or $args[0] > self::CASINO_MAX_SUM) {
            $player->sendMessage("§c§cМинимальная сумма должна быть > " . self::CASINO_MIN_SUM . " и < " . self::CASINO_MAX_SUM);
            return false;
        }

        return true;
    }
}
?>
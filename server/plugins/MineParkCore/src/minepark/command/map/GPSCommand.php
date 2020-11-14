<?php
namespace minepark\command\map;

use minepark\Mapper;

use minepark\Sounds;
use pocketmine\Player;
use minepark\Permission;
use pocketmine\event\Event;

use minepark\command\Command;
use pocketmine\level\Position;

class GPSCommand extends Command
{
    public const CURRENT_COMMAND = "gps";

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
        if(self::argumentsCount(2, $args)) {
            if(!is_numeric($args[0]) or !is_numeric($args[1])) {
                $player->sendMessage("§cУказана неверная позиция X Z");
                return;
            }

            $player->gps = new Position($args[0], $player->getY(), $args[1], $player->getLevel());

            $player->sendMessage("§aМаршрут к указанной точке проложен;");
            $player->sendMessage("§6Следуйте по направлению указателей навигатора!");
            return;
        }
        
        if(self::argumentsCount(1, $args)) {
            $point = $args[0];

            $player->gps = $this->getCore()->getMapper()->getPointPosition($point);

            if($player->gps == false) {
                $player->gps = null;

                $player->sendMessage("§6Места §e$point §6на карте не существует!");
            } else {
                $player->sendMessage("§aМаршрут к точке §e$point §aобновлен;");
                $player->sendMessage("§6Следуйте по направлению указателей навигатора!");
            }
            return;
        }

        $player->gps = null;
        $player->bar = null;
            
        $this->sendInformationWindow($player);

        $player->sendSound(Sounds::OPEN_NAVIGATOR);
    }

    private function sendInformationWindow(Player $player) 
    {
        $form = "";
        $x = floor($player->getX()); 
        $z = floor($player->getZ());

        $form .= "§4(§7gps§4) §7места рядом: §d/gpsnear\n";
        $form .= "§4(§7gps§4) §7Проложить маршрут: §d/gps <назв.места>\n";
        $form .= "§4(§7gps§4) §7Проложить к точке: §d/gps <X> <Z>\n";
        $form .= "§4(§7gps§4) §7В некоторых местах острова навигатор может работать неправильно из за плохого подключения к спутникам\n";
        $form .= "§4(§7gps§4) §9Ваша позиция§7(X : Z)§9: §6$x : $z\n";

        $form .= "\n§7> §6Общественные места: §a" . implode(', ', $this->getCore()->getMapper()->getPointsByGroup(Mapper::GENERIC_POINT_GROUP));
        $form .= "\n§7> §6Торговые площадки: §a" . implode(', ', $this->getCore()->getMapper()->getPointsByGroup(Mapper::MARKETPLACE_POINT_GROUP));
        $form .= "\n§7> §6Арендная недвижимость: §a" . implode(', ', $this->getCore()->getMapper()->getPointsByGroup(Mapper::REALTY_POINT_GROUP));

        $player->sendWindowMessage($form, "§9|============#НАВИГАТОР#============|");
    }
}
?>
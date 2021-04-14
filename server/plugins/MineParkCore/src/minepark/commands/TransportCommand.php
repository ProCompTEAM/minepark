<?php
namespace minepark\commands;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\vehicles\Vehicles;

class TransportCommand extends Command
{
    public const CURRENT_COMMAND = "t";

    private Vehicles $vehicles;

    public function __construct()
    {
        $this->vehicles = Components::getComponent(Vehicles::class);
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
            Permissions::OPERATOR,
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            return $player->sendMessage("Неправильное использование команды. /t spawn <машина>");
        }
        
        if ($args[0] === "spawn") {
            if (!self::argumentsMin(2, $args)) {
                return $player->sendMessage("Неправильное использование команды. /t spawn <машина>");
            }

            if (!$this->spawnCar($player, $args[1])) {
                return $player->sendMessage("Неверное название модели машины!");
            }

            $player->sendMessage("Машина успешно создана.");
        }
    }

    private function spawnCar(MineParkPlayer $player, string $model) : bool
    {
        return $this->vehicles->createVehicle($model, $player->getLevel(), $player->asVector3(), $player->getYaw());
    }
}
?>
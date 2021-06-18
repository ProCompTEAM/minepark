<?php
namespace minepark\commands;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\vehicles\Vehicles;
use minepark\defaults\Sounds;

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
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        if(self::argumentsNo($args)) {
            $player->sendMessage("Неправильное использование команды. /t spawn <машина>");

            return;
        }

        $subCommand = $args[0];
        
        if ($subCommand == "spawn") {
            if (!$player->isAdministrator()) {
                $player->sendMessage("Необходимо запросить доступ для выполнения этой команды!");

                return;
            }

            if (!self::argumentsMin(2, $args)) {
                $player->sendMessage("Неправильное использование команды. /t spawn <машина>");

                return;
            }

            if (!$this->spawnCar($player, $args[1])) {
                $player->sendMessage("Неверное название модели машины!");

                return;
            }

            $player->sendMessage("Машина успешно создана.");
        } elseif($subCommand == "station") {
            //TODO: Add check: is @driver in @vehicle only

            if (!isset($args[1])) {
                $player->sendMessage("Неправильное использование команды. /t station <порядковый номер>");

                return;
            }

            $this->broadcastTrainStationSound($player, Sounds::STATION, 0);
            $this->broadcastTrainStationSound($player, $this->getSoundForStationNumber($args[1]), 2);
        }
    }

    private function spawnCar(MineParkPlayer $player, string $model) : bool
    {
        return $this->vehicles->createVehicle($model, $player->getLevel(), $player->asVector3(), $player->getYaw());
    }

    private function broadcastTrainStationSound(MineParkPlayer $driver, string $sound, int $delay)
    {
        
    }

    private function getSoundForStationNumber(string $stationCode) : string
    {
        return Sounds::STATION + $stationCode;
    }
}
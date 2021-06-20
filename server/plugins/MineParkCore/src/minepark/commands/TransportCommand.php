<?php
namespace minepark\commands;

use minepark\Tasks;
use minepark\Components;
use pocketmine\event\Event;
use minepark\defaults\Sounds;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\defaults\TimeConstants;
use minepark\defaults\VehicleConstants;
use minepark\common\player\MineParkPlayer;
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
        
        if($subCommand == "spawn") {
            if(!$player->isAdministrator()) {
                $player->sendMessage("Необходимо запросить доступ для выполнения этой команды!");

                return;
            }

            if(!self::argumentsMin(2, $args)) {
                $player->sendMessage("Неправильное использование команды. /t spawn <машина>");

                return;
            }

            if(!$this->spawnCar($player, $args[1])) {
                $player->sendMessage("Неверное название модели машины!");

                return;
            }

            $player->sendMessage("Машина успешно создана.");
        } elseif($subCommand == "station") {
            //TODO: Add check: is @driver in @vehicle only

            if(!self::argumentsMin(2, $args)) {
                $player->sendMessage("Неправильное использование команды. /t station <порядковый номер>");

                return;
            }

            $this->broadcastTrainStation($player, Sounds::TRAIN_STATION, 0);
            $this->broadcastTrainStation($player, $this->getSoundForStationNumber($args[1]), 3);
        }
    }

    public function broadcastTrainStationSound(MineParkPlayer $driver, string $sound)
    {
        foreach($this->getCore()->getRegionPlayers($driver->getPosition(), VehicleConstants::PLAYER_NEAR_STATION_DISTANCE) as $player) {
            $player = MineParkPlayer::cast($player);
            $player->sendSound($sound);
        }
    }

    private function broadcastTrainStation(MineParkPlayer $driver, string $sound, int $delaySeconds)
    {
        Tasks::registerDelayedAction(
            $delaySeconds * TimeConstants::ONE_SECOND_TICKS, 
            [$this, "broadcastTrainStationSound"],
            [$driver, $sound]
        );
    }

    private function getSoundForStationNumber(string $stationCode) : string
    {
        return Sounds::TRAIN_STATION . $stationCode;
    }

    private function spawnCar(MineParkPlayer $player, string $model) : bool
    {
        return $this->vehicles->createVehicle($model, $player->getWorld(), $player->getPosition()->asVector3(), $player->getLocation()->getYaw());
    }
}
<?php
namespace minepark\commands\map;

use Exception;

use minepark\Providers;
use pocketmine\event\Event;
use minepark\defaults\Sounds;

use pocketmine\world\Position;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\defaults\MapConstants;
use minepark\models\dtos\MapPointDto;
use minepark\common\player\MineParkPlayer;
use minepark\providers\MapProvider;

class GPSCommand extends Command
{
    public const CURRENT_COMMAND = "gps";

    private const FLOATING_TEXT_TAG = "GPS";

    private const GPS_LIGHTS_SUBCOMMAND_NAME = "lights";

    private const MINIMAL_X = -100000;

    private const MINIMAL_Z = -100000;

    private const MAXIMAL_X = 100000;

    private const MAXIMAL_Z = 100000;

    private MapProvider $mapProvider;

    public function __construct()
    {
        $this->mapProvider = Providers::getMapProvider();
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
        if(self::argumentsCount(2, $args)) {
            $this->initializeCoordinatesGps($player, $args);
        } else if(self::argumentsCount(1, $args)) {
            if($args[0] === self::GPS_LIGHTS_SUBCOMMAND_NAME) {
                $this->updateLights($player);
            } else {
               $this->initializePointGps($player, $args);
            }
        } else {
            $this->disableGPS($player);
            $this->showInformation($player);
        }
    }

    private function initializeCoordinatesGps(MineParkPlayer $player, array $args)
    {
        if(!is_numeric($args[0]) or !is_numeric($args[1])) {
            $player->sendMessage("CommandGPSnoXZ");
            return;
        }

        $x = $args[0];
        $z = $args[1];

        if($x > self::MAXIMAL_X or $z > self::MAXIMAL_Z
            or $x < self::MINIMAL_X or $z < self::MINIMAL_Z) {
            $player->sendMessage("CommandGPSCoordinatesBig");
            return;
        }

        $player->getStatesMap()->gps = new Position($x, $player->getLocation()->getY(), $z, $player->getWorld());

        $player->sendMessage("CommandGPSPath1");
        $player->sendMessage("CommandGPSPath2");
    }

    private function initializePointGps(MineParkPlayer $player, array $args)
    {
        $pointName = $args[0];

        $pointPosition = $this->mapProvider->getPointPosition($pointName);

        if(!isset($pointPosition)) {
            $player->sendLocalizedMessage("{CommandGPSNoPointPart1} $pointName {CommandGPSNoPointPart2}");
            return;
        }

        $player->getStatesMap()->gps = $pointPosition;
        $player->sendLocalizedMessage("{CommandGPSToPointPart1} $pointName {CommandGPSToPointPart2}");
        $player->sendMessage("CommandGPSPath2");
    }

    private function updateLights(MineParkPlayer $player)
    {
        $gpsLightsVisible = $player->getStatesMap()->gpsLightsVisible;

        if(!$gpsLightsVisible) {
            $this->showLights($player);
        } else {
            $this->hideLights($player);
        }

        $player->getStatesMap()->gpsLightsVisible = !$gpsLightsVisible;
    }

    private function sendInformationWindow(MineParkPlayer $player) 
    {
        $x = $player->getLocation()->getFloorX();
        $z = $player->getLocation()->getFloorZ();

        $form  = "§4(§7gps§4) §7Места рядом: §d/gpsnear\n";
        $form .= "§4(§7gps§4) §7Подсветить точки: §d/gps lights\n";
        $form .= "§4(§7gps§4) §7Проложить маршрут: §d/gps <назв.места>\n";
        $form .= "§4(§7gps§4) §7Проложить к точке: §d/gps <X> <Z>\n";
        $form .= "§4(§7gps§4) §7В некоторых местах острова навигатор может работать неправильно из за плохого подключения к спутникам\n";
        $form .= "§4(§7gps§4) §9Ваша позиция§7(X : Z)§9: §6$x : $z\n";

        $form .= "\n§7> §6Общественные места: §a" . implode(', ', $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_GENERIC));
        $form .= "\n§7> §6Торговые площадки: §a" . implode(', ', $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_MARKETPLACE));
        $form .= "\n§7> §6Арендная недвижимость: §a" . implode(', ', $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_REALTY));

        $player->sendWindowMessage($form, "§9|============#НАВИГАТОР#============|");
    }

    private function showLights(MineParkPlayer $player)
    {
        $genericPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_GENERIC, false);
        $marketPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_MARKETPLACE, false);
        $realtyPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_REALTY, false);

        $this->showLightsForPoints($player, $genericPoints, "§b§a❒ ");
        $this->showLightsForPoints($player, $marketPoints, "§b§e＄ ");
        $this->showLightsForPoints($player, $realtyPoints, "§b§9⌂ ");

        $player->sendMessage("CommandGPSShowLights");
    }

    private function showLightsForPoints(MineParkPlayer $player, array $points, string $prefix) 
    {
        foreach($points as $point) {
            $point = $this->castToMapPointDto($point);

            if(strtolower($player->getWorld()->getDisplayName()) === $point->world) {
                $world = $this->getServer()->getWorldManager()->getWorldByName($point->world);
                $position = new Position($point->x, $point->y, $point->z, $world);
                $player->setFloatingText($position, $prefix . $point->name, self::FLOATING_TEXT_TAG);
            }
        }

        $player->showFloatingTexts();
    }

    private function hideLights(MineParkPlayer $player)
    {
        $floatingTexts = $player->getFloatingTextsByTag(self::FLOATING_TEXT_TAG);

        foreach($floatingTexts as $floatingText) {
            $player->unsetFloatingText($floatingText);
        }

        $player->sendMessage("CommandGPSHideLights");
    }

    private function disableGPS(MineParkPlayer $player)
    {
        $player->getStatesMap()->gps = null;
        $player->getStatesMap()->bar = null;
    }

    private function showInformation(MineParkPlayer $player)
    {
        $this->sendInformationWindow($player);

        $player->sendSound(Sounds::OPEN_NAVIGATOR);
    }

    private function castToMapPointDto(object $point) : MapPointDto
    {
        if($point instanceof MapPointDto) {
            return $point;
        } else {
            throw new Exception("Object isn't MapPointDto");
        }
    }
}
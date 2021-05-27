<?php
namespace minepark\commands\map;

use Exception;

use minepark\Providers;
use pocketmine\event\Event;
use minepark\defaults\Sounds;

use pocketmine\level\Position;
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
        if (self::argumentsCount(2, $args)) {
            return $this->initializeCoordinatesGps($player, $args);
        } else if (self::argumentsCount(1, $args)) {
            if($args[0] === "lights") {
                return $this->updateLights($player);
            } else {
                return $this->initializePointGps($player, $args);
            }
        }

        $player->getStatesMap()->gps = null;
        $player->getStatesMap()->bar = null;
            
        $this->sendInformationWindow($player);

        $player->sendSound(Sounds::OPEN_NAVIGATOR);
    }

    private function initializeCoordinatesGps(MineParkPlayer $player, array $args)
    {
        if (!is_numeric($args[0]) or !is_numeric($args[1])) {
            $player->sendMessage("CommangGPSnoXZ");
            return;
        }

        $player->getStatesMap()->gps = new Position($args[0], $player->getY(), $args[1], $player->getLevel());

        $player->sendMessage("CommandGPSPath1");
        $player->sendMessage("CommandGPSPath2");
    }

    private function initializePointGps(MineParkPlayer $player, array $args)
    {
        $point = $args[0];

        $gps = $this->mapProvider->getPointPosition($point);

        if (!isset($gps)) {
            return $player->sendLocalizedMessage("{CommandGPSNoPointPart1} $point {CommandGPSNoPointPart2}");
        }

        $player->getStatesMap()->gps = $gps;
        $player->sendLocalizedMessage("{CommandGPSToPointPart1} $point {CommandGPSToPointPart2}");
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
        $x = floor($player->getX()); 
        $z = floor($player->getZ());

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

        $player->sendMessage("§9Теперь вы видите базовые точки прямо на карте!");
    }

    private function showLightsForPoints(MineParkPlayer $player, array $points, string $prefix) 
    {
        foreach($points as $point) {
            $point = $this->castToMapPointDto($point);

            if(strtolower($player->getLevel()->getName()) === $point->level) {
                $level = $this->getServer()->getLevelByName($point->level);
                $position = new Position($point->x, $point->y, $point->z, $level);
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

        $player->sendMessage("§6Точки навигации были скрыты.");
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
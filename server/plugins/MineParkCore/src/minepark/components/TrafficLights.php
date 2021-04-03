<?php
namespace minepark\components;

use minepark\Tasks;
use minepark\Providers;
use pocketmine\level\Position;
use minepark\defaults\MapConstants;
use minepark\providers\MapProvider;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;

class TrafficLights extends Component
{
    private const RED_SIGNAL_TEXT =   "§l§c●\n§l§8●";
    private const GREEN_SIGNAL_TEXT = "§l§8●\n§l§a●";

    private const TRAFFIC_LIGHTS_TAG = "TRAFFIC_LIGHTS";

    private MapProvider $mapProvider;

    private array $lightPositionsVariation1 = [];
    private array $lightPositionsVariation2 = [];

    private bool $isVariationSwitched = false;

    public function __construct()
    {
        Tasks::registerRepeatingAction(TimeConstants::TRAFFIC_LIGHTS_UPDATE_INTERVAL, [$this, "updateLights"]);

        $this->mapProvider = Providers::getMapProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }
    
    public function updateLights()
    {
        $this->loadVariation1LightsPoints();
        $this->loadVariation2LightsPoints();
        $this->removeAllTrafficLights();
        $this->showTrafficLights();
        $this->switchVariation();
    }

    private function loadVariation1LightsPoints()
    {
        $lightPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_TRAFFIC_LIGHT1, false);

        foreach($lightPoints as $point) {
            $level = $this->getCore()->getServer()->getLevelByName($point->level);
            array_push($this->lightPositionsVariation1, new Position($point->x, $point->y, $point->z, $level));
        }
    }

    private function loadVariation2LightsPoints()
    {
        $lightPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_TRAFFIC_LIGHT2, false);

        foreach($lightPoints as $point) {
            $level = $this->getCore()->getServer()->getLevelByName($point->level);
            array_push($this->lightPositionsVariation2, new Position($point->x, $point->y, $point->z, $level));
        }
    }

    private function switchVariation()
    {
        $this->isVariationSwitched = !$this->isVariationSwitched;
    }

    private function removeAllTrafficLights()
    {
        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);
            foreach($player->getFloatingTextsByTag(self::TRAFFIC_LIGHTS_TAG) as $floatingText) {
                $player->unsetFloatingText($floatingText);
            }
        }
    }

    private function showTrafficLights()
    {
        $signalText1 = $this->isVariationSwitched ? self::GREEN_SIGNAL_TEXT : self::RED_SIGNAL_TEXT;
        $signalText2 = $this->isVariationSwitched ? self::RED_SIGNAL_TEXT : self::GREEN_SIGNAL_TEXT;

        foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
            $this->createFloatingTexts($player, $this->lightPositionsVariation1, $signalText1);
            $this->createFloatingTexts($player, $this->lightPositionsVariation2, $signalText2);
        }
    }

    private function createFloatingTexts(MineParkPlayer $player, array $positions, string $signalText)
    {
        foreach($positions as $position) {
            $player->setFloatingText($position, $signalText, self::TRAFFIC_LIGHTS_TAG);
        }
        $player->showFloatingTexts();
    }
}
?>
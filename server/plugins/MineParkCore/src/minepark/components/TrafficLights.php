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

    private const TRAFFIC_LIGHTS_TAG = "TRAFFIC_LIGHT";

    private MapProvider $mapProvider;

    private array $lightPositionsVariation1 = [];
    private array $lightPositionsVariation2 = [];

    private bool $isVariationSwitched = false;

    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::TRAFFIC_LIGHTS_UPDATE_INTERVAL, [$this, "updateLights"]);

        $this->mapProvider = Providers::getMapProvider();

        $this->loadVariation1LightsPoints();
        $this->loadVariation2LightsPoints();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }
    
    public function updateLights()
    {
        $this->showTrafficLights();
        $this->switchVariation();
    }

    private function loadVariation1LightsPoints()
    {
        $lightPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_TRAFFIC_LIGHT1, false);

        foreach($lightPoints as $point) {
            $level = $this->getServer()->getLevelByName($point->level);
            array_push($this->lightPositionsVariation1, new Position($point->x, $point->y, $point->z, $level));
        }
    }

    private function loadVariation2LightsPoints()
    {
        $lightPoints = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_TRAFFIC_LIGHT2, false);

        foreach($lightPoints as $point) {
            $level = $this->getServer()->getLevelByName($point->level);
            array_push($this->lightPositionsVariation2, new Position($point->x, $point->y, $point->z, $level));
        }
    }

    private function showTrafficLights()
    {
        $signalText1 = $this->isVariationSwitched ? self::GREEN_SIGNAL_TEXT : self::RED_SIGNAL_TEXT;
        $signalText2 = $this->isVariationSwitched ? self::RED_SIGNAL_TEXT : self::GREEN_SIGNAL_TEXT;

        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->updateFloatingTexts($player, $this->lightPositionsVariation1, $signalText1);
            $this->updateFloatingTexts($player, $this->lightPositionsVariation2, $signalText2);
        }
    }

    private function switchVariation()
    {
        $this->isVariationSwitched = !$this->isVariationSwitched;
    }

    private function updateFloatingTexts(MineParkPlayer $player, array $positions, string $signalText)
    {
        foreach($positions as $position) {
            $floatingText = $player->getFloatingText($position);
            if(!isset($floatingText)) {
                $player->setFloatingText($position, $signalText, self::TRAFFIC_LIGHTS_TAG);
            } else {
                $floatingText->text = $signalText;
                $player->updateFloatingText($floatingText);
            }
        }
        $player->showFloatingTexts();
    }
}
?>
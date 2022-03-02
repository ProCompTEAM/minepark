<?php

namespace minepark\components\vehicles;

use minepark\components\base\Component;
use minepark\components\vehicles\models\base\BaseTrain;
use minepark\components\vehicles\models\Train;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\EventList;
use minepark\Events;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockLegacyMetadata;
use pocketmine\block\Rail;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\Position;
use pocketmine\world\World;

class Trains extends Component
{
    private array $trains;

    public function initialize()
    {
        $this->loadTrains();

        Events::registerEvent(EventList::PLAYER_INTERACT_EVENT, [$this, "onInteract"]);
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function getTrains()
    {
        return $this->trains;
    }

    public function getTrain(string $name) : ?string
    {
        return isset($this->trains[$name]) ? $this->trains[$name] : null;
    }

    public function spawnTrain(string $name, Location $location) : bool
    {
        $trainClassName = $this->getTrain($name);

        if($trainClassName === null) {
            return false;
        }

        $world = $location->getWorld();
        $vector = $location->floor();

        $blockBelow = $world->getBlock($vector);

        if(!$blockBelow instanceof Rail) {
            return false;
        }

        if($blockBelow->getShape() !== 0 and $blockBelow->getShape() !== 1) {
            return false;
        }

        $train = new $trainClassName($location);
        $train->saveNBT();

        $train->spawnToAll();

        return true;
    }

    public function onInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();

        if($block instanceof Rail) {
            $event->getPlayer()->sendMessage("it's " . $block->getShape());
        }
    }

    private function loadTrains()
    {
        $this->trains = [
            "train" => Train::class
        ];

        foreach($this->getTrains() as $name => $class) {
            EntityFactory::getInstance()->register($class, function(World $world, CompoundTag $nbt) use($class) : BaseTrain {
                return new $class(EntityDataHelper::parseLocation($nbt, $world), $nbt);
            }, [$name]);
        }
    }
}
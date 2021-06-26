<?php
namespace minepark\components;

use minepark\Events;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

class WorldProtector extends Component
{
    private int $minimumX;

    private int $minimumZ;

    private int $maximumX;

    private int $maximumZ;

    private string $level;

    public function initialize()
    {
        Events::registerEvent(EventList::BLOCK_BREAK_EVENT, [$this, "applyBlockUpdateSettings"]);
        Events::registerEvent(EventList::BLOCK_PLACE_EVENT, [$this, "applyBlockUpdateSettings"]);

        $this->loadConfiguration();
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function applyBlockUpdateSettings(BlockBreakEvent | BlockPlaceEvent $event)
    {
        if($event->getPlayer()->canBuild()) {
            $event->uncancel();
            return;
        }

        if($this->isInRange($event->getBlock()->getPos())) {
            $event->cancel();
        }
    }

    public function isInRange(Position $position) : bool
    {
        return (
            $position->getWorld()->getDisplayName() == $this->getWorldName() and
            $position->getX() >= $this->getMinimumX() and
            $position->getZ() >= $this->getMinimumZ() and
            $position->getX() <= $this->getMaximumX() and
            $position->getZ() <= $this->getMaximumZ()
        );
    }

    private function loadConfiguration()
    {
        $file = $this->getCore()->getTargetDirectory() . "world-protector.yml";
        $defaultLevelName = $this->getServer()->getWorldManager()->getDefaultWorld()->getDisplayName();

        $config = new Config($file, Config::YAML, [
            "Level" => $defaultLevelName,
            "MinimumX" => -128,
            "MinimumZ" => -128,
            "MaximumX" => 128,
            "MaximumZ" => 128,
        ]);
        
        $this->level = $config->get("Level");
        $this->minimumX = $config->get("MinimumX");
        $this->minimumZ = $config->get("MinimumZ");
        $this->maximumX = $config->get("MaximumX");
        $this->maximumZ = $config->get("MaximumZ");
    }
    
    private function getMinimumX() : int
    {
        return $this->minimumX;
    }

    private function getMinimumZ() : int
    {
        return $this->minimumZ;
    }

    private function getMaximumX() : int
    {
        return $this->maximumX;
    }

    private function getMaximumZ() : int
    {
        return $this->maximumZ;
    }

    private function getWorldName() : string
    {
        return $this->level;
    }
}
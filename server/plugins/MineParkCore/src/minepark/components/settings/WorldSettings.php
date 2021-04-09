<?php
namespace minepark\components\settings;

use minepark\Events;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\level\ChunkLoadEvent;

class WorldSettings extends Component
{
    public function initialize()
    {
        Events::registerEvent(EventList::BLOCK_BURN_EVENT, [$this, "applyBlockBurnSettings"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function applyBlockBurnSettings(BlockBurnEvent $event)
    {
        $event->setCancelled();
    }
}

?>
<?php
namespace minepark\components\settings;

use minepark\Events;
use minepark\defaults\EventList;
use minepark\components\base\Component;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\level\ChunkLoadEvent;

class WorldSettings extends Component
{
    public function __construct()
    {
        Events::registerEvent(EventList::BLOCK_BURN_EVENT, [$this, "applyBlockBurnSettings"]);
        Events::registerEvent(EventList::CHUNK_LOAD_EVENT, [$this, "chunkLoadSettings"]);
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

    public function chunkLoadSettings(ChunkLoadEvent $event)
    {
        if ($event->isNewChunk()) {
            $x = $event->getChunk()->getX();
            $z = $event->getChunk()->getZ();

            $event->getLevel()->unloadChunk($x, $z);
        }
    }
}

?>
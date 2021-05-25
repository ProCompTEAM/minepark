<?php
namespace minepark\components\map;

use minepark\Tasks;
use minepark\components\base\Component;
use minepark\defaults\TimeConstants;
use pocketmine\entity\object\ItemEntity;

class ClearLagg extends Component
{
    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::CLEAR_LAGG_INTERVAL - TimeConstants::CLEAR_LAGG_WARN_ON_TIME_LEFT, [$this, "sendWarning"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function sendWarning()
    {
        $this->getServer()->broadcastMessage("Очистка лежащих предметов через ". TimeConstants::CLEAR_LAGG_WARN_ON_TIME_LEFT ." секунд!");
        Tasks::registerDelayedAction(TimeConstants::ONE_SECOND_TICKS * TimeConstants::CLEAR_LAGG_WARN_ON_TIME_LEFT, [$this, "clearItems"]);
    }

    public function clearItems()
    {
        $itemsAmount = 0;
        foreach ($this->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $entity) {
                if ($entity instanceof ItemEntity) {
                    $entity->close();
                    $itemsAmount += $entity->getItem()->getCount();
                }
            }
        }
        $this->getServer()->broadcastMessage("Было удалено $itemsAmount предметов");
    }
}
?>
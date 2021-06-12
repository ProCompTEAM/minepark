<?php
namespace minepark\components\map;

use minepark\common\player\MineParkPlayer;
use minepark\Tasks;
use minepark\components\base\Component;
use minepark\defaults\TimeConstants;
use pocketmine\entity\object\ItemEntity;

class ClearLagg extends Component
{
    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::CLEAR_LAGG_INTERVAL, [$this, "clearItems"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function clearItems()
    {
        foreach ($this->getServer()->getWorlds() as $level) {
            foreach ($level->getEntities() as $entity) {

                if ($entity instanceof ItemEntity) {
                    $entity->close();
                }

                if ($entity instanceof MineParkPlayer) {
                    $entity->sendTip("§dКоммунальные службы очистили среду от мусора!");
                }

            }
        }
    }
}
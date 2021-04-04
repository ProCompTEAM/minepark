<?php
namespace minepark\components;

use minepark\Tasks;
use minepark\components\base\Component;
use minepark\defaults\TimeConstants;

class StatusBar extends Component
{
    public function initialize()
    {
        Tasks::executeActionWithTicksInterval(TimeConstants::ONE_SECOND_TICKS, [$this, "updateAll"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }

    public function updateAll()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            if($player->getStatesMap()->bar != null) {
                $player->sendTip($player->getStatesMap()->bar);
            }
        }
    }
}
?>
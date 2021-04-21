<?php
namespace minepark\components\map;

use minepark\Tasks;
use minepark\Providers;
use minepark\defaults\MapConstants;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;
use minepark\providers\MapProvider;

class PlayersLocation extends Component
{
    private MapProvider $mapProvider;

    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::SHOW_PLAYERS_LIST_INTERVAL, [$this, "broadcast"]);

        $this->mapProvider = Providers::getMapProvider();
    }

    public function getAttributes() : array
    {
        return [
        ];
    }
    
    public function broadcast()
    {
        $points = $this->mapProvider->getPointsByGroup(MapConstants::POINT_GROUP_GENERIC);
        $pointsCount = array();
        $playerCounted = array();
        
        $evenOnePlayer = false;

        foreach($points as $point) {
            $pointName = $point;
            $pointsCount[$pointName] = 0;
            
            foreach($this->getServer()->getOnlinePlayers() as $plr) {
                if (!empty($playerCounted[$plr->getName()])) {
                    continue;
                }

                $vector1 = $plr->asVector3();
                $vector2 = $this->mapProvider->getPointPosition($pointName)->asVector3();
                
                if ($vector1->distance($vector2) < 50) {
                    $evenOnePlayer = true;
                    $playerCounted[$plr->getName()] = true;
                    
                    $pointsCount[$pointName]++;
                }
            }

        }
        
        if (!$evenOnePlayer) {
            return;
        }

        $this->getServer()->broadcastMessage("NotifyLabel");
        
        foreach($pointsCount as $place => $val) {
            if ($val == 0) {
                continue;
            }

            $this->getServer()->broadcastMessage('§b - Возле места §e"'.$place.'"§b сейчас §e'.$val.'§b человек(a).');
        }
    }
}
?>
<?php
namespace minepark\components;

use minepark\Tasks;
use minepark\Providers;
use minepark\defaults\MapConstants;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;

class NotifyPlayers extends Component
{
    public function __construct()
    {
        Tasks::registerRepeatingAction(TimeConstants::SHOW_PLAYERS_LIST_INTERVAL, [$this, "broadcast"]);
    }

    public function getAttributes() : array
    {
        return [
        ];
    }
    
    public function broadcast()
    {
        $points = Providers::getMapProvider()->getPointsByGroup(MapConstants::POINT_GROUP_GENERIC);
        $pointsCount = array();
        $playerCounted = array();
        
        $evenOnePlayer = false;

        foreach($points as $point) {
            $pointName = $point;
            $pointsCount[$pointName] = 0;
            
            foreach($this->getCore()->getServer()->getOnlinePlayers() as $plr) {
                if (!empty($playerCounted[$plr->getName()])) {
                    continue;
                }

                $vector1 = $plr->asVector3();
                $vector2 = Providers::getMapProvider()->getPointPosition($pointName)->asVector3();
                
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

        $this->getCore()->getServer()->broadcastMessage("NotifyLabel");
        
        foreach($pointsCount as $place => $val) {
            if ($val == 0) {
                continue;
            }

            $this->getCore()->getServer()->broadcastMessage('§b - Возле места §e"'.$place.'"§b сейчас §e'.$val.'§b человек(a).');
        }
    }
}
?>
<?php
namespace minepark\components;

use minepark\Core;
use minepark\Mapper;
use minepark\utils\CallbackTask;
use minepark\components\base\Component;

class NotifyPlayers extends Component
{
    public $mapper;

    public function __construct()
    {
        $this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "broadcast"]), 20 * 763);
        $this->mapper = $this->getCore()->getMapper();
    }

    public function getAttributes() : array
    {
        return [
        ];
    }
    
    public function broadcast()
    {
        $points = $this->mapper->getPointsByGroup(Mapper::POINT_GROUP_GENERIC);
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
                $vector2 = $this->mapper->getPointPosition($pointName)->asVector3();
                
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
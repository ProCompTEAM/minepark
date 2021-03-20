<?php
//MinecraftPEClint fix from 12.07.2017, upd 30.06.2019

namespace minepark\utils;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use minepark\common\player\MineParkPlayer;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;

use minepark\utils\CallbackTask;

class FixSignEvent
{
    public $position;
    public $e;
    
    public function __construct($event)
    {
        $this->position = $event->getBlock();
        $this->e = $event;
    }
    
    public function getEvent()
    {
        return new FixSignChangeEvent($this->e->getBlock(), $this->e->getPlayer(), $this->getLines());
    }
    
    public function getLines()
    {
        $pos = $this->position;
        foreach($pos->getLevel()->getTiles() as $tile)
        {
            if($tile instanceof Sign)
            {
                if(floor($tile->getX()) == floor($pos->getX()) and floor($tile->getY()) == floor($pos->getY())
                    and floor($tile->getZ()) == floor($pos->getZ()) and $tile->getLevel() == $pos->getLevel())
                {
                    return $tile->getText();
                }
            }
        }
    }
}

class FixSignChangeEvent extends SignChangeEvent
{
    public function setLine(int $index, string $line) : void
    {
        $pos = $this->getBlock();
        foreach($pos->getLevel()->getTiles() as $tile)
        {
            if($tile instanceof Sign)
            {
                if(floor($tile->getX()) == floor($pos->getX()) and floor($tile->getY()) == floor($pos->getY())
                    and floor($tile->getZ()) == floor($pos->getZ()) and $tile->getLevel() == $pos->getLevel())
                {
                    $l = $tile->getText();
                    $l[$index] = $line;
                    $lines = $tile->setText($l[0],$l[1],$l[2],$l[3]);
                }
            }
        }
    }
}
?>
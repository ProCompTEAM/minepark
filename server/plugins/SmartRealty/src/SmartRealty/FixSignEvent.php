<?php
//special MinecraftPEClint fix from 12.07.2017

namespace SmartRealty;

use pocketmine\block\Block;
use pocketmine\block\tile\Sign;
use pocketmine\event\Cancellable;
use pocketmine\player\Player;
use pocketmine\event\block\SignChangeEvent;

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
        foreach($pos->getWorld()->getTiles() as $tile)
        {
            if($tile instanceof Sign)
            {
                $position = $tile->getPosition();
                if(floor($position->getX()) == floor($pos->getX()) and floor($position->getY()) == floor($pos->getY())
                    and floor($position->getZ()) == floor($pos->getZ()) and $position->getWorld() == $pos->getWorld())
                {
                    return $tile->getText();
                }
            }
        }
    }
}

class FixSignChangeEvent extends SignChangeEvent
{
    public function setLine(int $index, string $text) : void
    {
        $pos = $this->getBlock();
        $tile = $pos->getPosition()->getWorld()->getTile($pos->getPosition()->asVector3());

        $l = $tile->getText();
        $l[$index] = $text;
        $lines = $tile->setText($l[0],$l[1],$l[2],$l[3]);
    }
}
?>
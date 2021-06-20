<?php
//special MinecraftPEClint fix from 12.07.2017

namespace SmartRealty;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\player\Player;
use pocketmine\tile\Sign;
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
                if(floor($tile->getX()) == floor($pos->getX()) and floor($tile->getY()) == floor($pos->getY())
                    and floor($tile->getZ()) == floor($pos->getZ()) and $tile->getWorld() == $pos->getWorld())
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
        foreach($pos->getWorld()->getTiles() as $tile)
        {
            if($tile instanceof Sign)
            {
                if(floor($tile->getX()) == floor($pos->getX()) and floor($tile->getY()) == floor($pos->getY())
                    and floor($tile->getZ()) == floor($pos->getZ()) and $tile->getWorld() == $pos->getWorld())
                {
                    $l = $tile->getText();
                    $l[$index] = $text;
                    $lines = $tile->setText($l[0],$l[1],$l[2],$l[3]);
                }
            }
        }
    }
}
?>
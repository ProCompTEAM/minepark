<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\player\Player;

class ClearCommand
{	
    private $main;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "cc") 
        {
            if($player->hasPermission("sc.command.cc")) 
            {
                $player->getInventory()->clearAll();
                
                $player->sendMessage("§bВаш инвентарь очищен!");
            }
            else return false;
        }
        
        return true;
    }
}

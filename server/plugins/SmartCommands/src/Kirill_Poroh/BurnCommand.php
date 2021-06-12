<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\player\Player;

class BurnCommand
{	
    private $main;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "burn") 
        {
            if($player->hasPermission("sc.command.burn")) 
            {
                if(!isset($args[0])) 
                {
                    $player->sendMessage("§cФормат: /burn <ник игрока>");
                    
                    return true;
                }
                
                $p = $this->main->getServer()->getPlayer($args[0]);
                $name = ($p == null ? $args[0] : $p->getName());
                
                if($p === null) 
                {
                    $player->sendMessage("§cИгрок $name вне игры!");
                    
                    return true;
                }
                
                $p->setOnFire(20);
                
                $player->sendMessage("§6!!! §eВы подожгли игрока §4$name §6!!!");
            }
            else return false;
        }
        
        return true;
    }
}

<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class SeeCommand
{	
    private $main;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "see") 
        {
            if($player->hasPermission("sc.command.see")) 
            {
                if($player->getEffect(16) != null) $player->removeEffect(16);
                else $player->addEffect(new EffectInstance(Effect::getEffect(16), 20 * 9999, 3));
            }
            else return false;
        }
        
        return true;
    }
}

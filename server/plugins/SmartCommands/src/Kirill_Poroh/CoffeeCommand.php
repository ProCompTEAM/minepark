<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

class CoffeeCommand
{	
    private $main;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "coffee") 
        {
            if($player->hasPermission("sc.command.coffee")) 
            {
                $player->addEffect(new EffectInstance(Effect::getEffect(1), 20 * 60, 3));
                $player->addEffect(new EffectInstance(Effect::getEffect(11), 20 * 60 * 2, 5));
                $player->addEffect(new EffectInstance(Effect::getEffect(8), 20 * 60, 3));
                $player->addEffect(new EffectInstance(Effect::getEffect(10), 20 * 15, 9));
            }
            else return false;
        }
        
        return true;
    }
}

<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;

class CoffeeCommand
{	
    private $main;
    public array $effects = [
        "speed",
        "resistance",
        "jump_boost",
        "regeneration"
    ];

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
                $effectManager = $player->getEffects();
                foreach($this->effects as $effectName) {
                    $effect = VanillaEffects::fromString($effectName);
                    $instance = new EffectInstance($effect, 20 * 60, 3, true);
                    $effectManager->add($instance);
                }
            }
            else return false;
        }
        
        return true;
    }
}

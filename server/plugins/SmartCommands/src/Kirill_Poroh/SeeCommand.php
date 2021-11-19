<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;

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
                $effect = StringToEffectParser::getInstance()->parse("night_vision");
                $effectInstance = new EffectInstance($effect, 20 * 9999, 3, false);

                if($player->getEffects()->has($effect)) {
                    $player->getEffects()->remove($effect);
                } else {
                    $player->getEffects()->add($effectInstance);
                }
            }
            else return false;
        }
        
        return true;
    }
}

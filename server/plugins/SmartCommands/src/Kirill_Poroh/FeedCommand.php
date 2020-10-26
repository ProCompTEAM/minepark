<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;
use pocketmine\entity\Effect;

class FeedCommand
{	
	private $main;

	public function __construct($MAIN)
	{
		$this->main = $MAIN;
	}
	
	public function run($command, $args, Player $player)
	{
		if($command == "feed") 
		{
			if($player->hasPermission("sc.command.feed")) 
			{
				$p = null;
				
				if(!isset($args[0])) $p = $player;
				else $p = $this->main->getServer()->getPlayer($args[0]);
				
				$name = ($p == null ? $args[0] : $p->getName());
				
				if($p === null) 
				{
					$player->sendMessage("§cИгрок $name вне игры!");
					
					return true;
				}
				
				$p->setFood(20);
				
				$player->sendMessage("§5♫♫♫ Теперь вы сыты =) ♫♫♫");
			}
			else return false;
		}
		
		return true;
	}
}

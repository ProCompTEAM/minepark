<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;
use pocketmine\level\Level;

class TimeCommand
{	
	private $main;

	public function __construct($MAIN)
	{
		$this->main = $MAIN;
	}
	
	public function run($command, $args, Player $player)
	{
		if($command == "day") 
		{
			if($player->hasPermission("sc.command.time")) 
			{
				$player->getLevel()->setTime(Level::TIME_DAY);
				$player->sendMessage("§9⌚ Вы включили §dдень §9в игровом мире §e" . $player->getLevel()->getName());
			}
			else return false;
		}
			
		if($command == "night") 
		{
			if($player->hasPermission("sc.command.time"))
			{				
				$player->getLevel()->setTime(Level::TIME_NIGHT);
				$player->sendMessage("§9⌚ Вы включили §1ночь §9в игровом мире §e" . $player->getLevel()->getName());
			}
			else return false;
		}
		
		return true;
	}
}

<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;

class FreezeCommand
{	
	private $main;

	public function __construct($MAIN)
	{
		$this->main = $MAIN;
	}
	
	public function run($command, $args, Player $player) : bool
	{
		if($command == "freeze") 
		{
			if($player->hasPermission("sc.command.freeze")) 
			{
				if(!isset($args[0])) 
				{
					$player->sendMessage("§cФормат: /freeze <ник игрока>");
					
					return true;
				}
				
				$p = $this->main->getServer()->getPlayer($args[0]);
				$name = ($p == null ? $args[0] : $p->getName());
				
				if($p === null) 
				{
					$player->sendMessage("§cИгрок $name вне игры!");
					
					return true;
				}
				
				$p->setImmobile(true);
				
				$this->main->getServer()->broadcastMessage($player->getName() . " §3заморозил §fигрока $name");
				
				return true;
			}
			else return false;
		}
		if($command == "unfreeze") 
		{
			if($player->hasPermission("sc.command.freeze")) 
			{
				if(!isset($args[0])) 
				{
					$player->sendMessage("§cФормат: /unfreeze <ник игрока>");
					
					return true;
				}
				
				$p = $this->main->getServer()->getPlayer($args[0]);
				$name = ($p == null ? $args[0] : $p->getName());
				
				if($p === null) 
				{
					$player->sendMessage("§cИгрок $name вне игры!");
					
					return true;
				}
				
				$p->setImmobile(false);
				
				$this->main->getServer()->broadcastMessage($player->getName() . " §3разморозил §fигрока $name");
				return true;
			}
			else return false;
		}
		return true;
	}
}

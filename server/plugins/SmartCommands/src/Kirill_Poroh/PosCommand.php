<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;

class PosCommand
{	
	private $main;

	public function __construct($MAIN)
	{
		$this->main = $MAIN;
	}
	
	public function run($command, $args, Player $player)
	{
		if($command == "pos")
		{
			if($player->hasPermission("sc.command.pos"))
			{
				$player->sendMessage
				(
					"§a☸ Ваша позиция: §3" . 
					floor($player->getX()) . " " .
					floor($player->getY()) . " " .
					floor($player->getZ())
				);
				
				return true;
			}
			else return false;
		}
		
		if($command == "getpos")
		{
			if($player->hasPermission("sc.command.getpos"))
			{
				if(!isset($args[0])) 
				{
					$player->sendMessage("§cФормат: /getpos <ник игрока>");
					
					return true;
				}
				
				$p = $this->main->getServer()->getPlayer($args[0]);
				$name = ($p == null ? $args[0] : $p->getName());
				
				if($p === null) 
				{
					$player->sendMessage("§cИгрок $name вне игры!");
					
					return true;
				}
				
				$player->sendMessage
				(
					"§a☸ Позиция игрока §6$name §f=" . 
					floor($p->getX()) . " " .
					floor($p->getY()) . " " .
					floor($p->getZ())
				);
			}
			else return false;
		}
		
		return true;
	}
}

<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;

class VanishCommand
{	
	private $main;

	public function __construct($MAIN)
	{
		$this->main = $MAIN;
	}
	
	public function run($command, $args, Player $player)
	{
		if($command == "v") 
		{
			if($player->hasPermission("sc.command.v")) 
			{
				if(isset($args[0]) and $args[0] == "show")
				{
					$this->show($player);
					
					$player->sendMessage("§2Теперь Вы снова стали видимым!");
				}
				else
				{
					$this->hide($player);
					
					$player->sendMessage("§1Вы невидимый сейчас!");
					$player->sendMessage("§5Стать видимым: §7/v show");
				}
			}
			else return false;
		}
		
		return true;
	}
	
	public function hide(Player $player)
	{
		foreach($this->main->getServer()->getOnlinePlayers() as $p) $p->hidePlayer($player);			
	}
	
	public function show(Player $player)
	{
		foreach($this->main->getServer()->getOnlinePlayers() as $p) $p->showPlayer($player);			
	}
}

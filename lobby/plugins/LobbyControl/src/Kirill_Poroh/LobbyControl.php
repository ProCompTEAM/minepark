<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;


class LobbyControl extends PluginBase implements Listener 
{	
	public function sendToRPServer(Player $player)
	{
		$player->transfer("minepark.ru", 19131);
	}
	
	public function sendToSurvivalServer(Player $player)
	{
		$player->transfer("minepark.ru", 19133);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, $args)
	{
		if($command->getName() == "rp") $this->sendToRPServer($sender);
		if($command->getName() == "survival") $this->sendToSurvivalServer($sender);
	}
}


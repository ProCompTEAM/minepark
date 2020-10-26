<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;

class StructMap
{	
	private $plugin;
	private $player;
	
	public function __construct($plugin, Player $player)
	{
		$this->plugin = $plugin;
		$this->player = $player;
	}

	public function get($item, $subitem = null)
	{
		$item = strtolower($item);
		
		$id = $this->plugin->getPlayerID($this->player);
		
		if($subitem === null)
			return ( isset($this->plugin->players_params[$id][$item]) ? $this->plugin->players_params[$id][$item] : null );
		else 
			return ( isset($this->plugin->players_params[$id][$item][strtolower($subitem)]) ? $this->plugin->players_params[$id][$item][$subitem] : null );
	}
	
	public function set($value, $item, $subitem = null)
	{
		$item = strtolower($item);
		$id = $this->plugin->getPlayerID($this->player);
		
		if($subitem === null)
			$this->plugin->players_params[$id][$item] = $value;
		else $this->plugin->players_params[$id][$item][strtolower($subitem)] = $value;
	}
	
	public function getChatFormat()
	{
		$format = "";
		
		if($this->player->isOp()) $format .= "§7⚑РУКОВОДСТВО ПАРКА ";
		else
		{
			switch($this->get("group"))
			{
				//II
				case "строитель":
					$format .= "§e✈Строитель парка ";
				break;
				
				case "модератор":
					$format .= "§1☀Смотрящий за порядком ";
				break;
				
				case "хелпер":
					$format .= "§5✚Работник справочного бюро ";
				break;
				
				case "тестировщик":
					$format .= "§3♫Ведущий тестировщик ";
				break;

				//I
				case "silver":
					$format .= "§7Silver Card ";
				break;
				
				case "gold":
					$format .= "§7~§6♞§eGOLD Card§6♞§7~ ";
				break;
				
				case "platinum":
					$format .= "§5€§7P§fL§7A§fT§7I§fN§7U§fM §7C§fa§7r§fd§5€ ";
				break;
				
				case "international":
					$format .= "§a★§0~§9International §1Community§0~§a★ ";
				break;
				
				case "resident":
					$format .= "§e♕§0-=§cResident Community§0=-§e♕ "; 
				break;
			}
		}
		
		return $format . "§f";
	}
}

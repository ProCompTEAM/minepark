<?php
declare(strict_types = 1);

namespace Kirill_Poroh\games;

use Kirill_Poroh\MCRPG;

use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;

use pocketmine\event\Event;

abstract class Game
{	
	protected $plugin;
	protected $player;
	protected $name;
	protected $level;
	
	protected $status;
	
	protected $players;
	
	protected $config;
	
	public function __construct(MCRPG $plugin, string $levelName, string $name)
	{
		$this->plugin = $plugin;
		
		$plugin->getServer()->loadLevel(strtolower($levelName));
		$this->level = $plugin->getServer()->getLevelByName(strtolower($levelName));
		
		$this->name = $name;
		
		$this->status = 0;
		
		$this->players = array();
		
		$this->config = new Config($plugin->getDirectory() . $this->name . ".game.json", Config::JSON);
	}
	
	public function finished()
	{
		$this->plugin->getLogger()->notice("Игра " . $this->getGameName() . " с ID " . $this->name . " в мире " . $this->level->getName() . " загружена!");
	}
	
	abstract public function joinPlayer(Player $player);
	
	abstract public function leavePlayer(Player $player);
	
	abstract public function command(Player $player, array $params);
	
	abstract public function handle(Event $event);
	
	public function getGameName() : string
	{
		return "Неизвестно";
	}
	
	public function getPlayers() : array
	{
		return $this->players;
	}
	
	public function getName() : string
	{
		return $this->name;
	}
	
	public function getStatus() : string
	{
		return $this->status;
	}
	
	protected function existsElement(array $arr, $object) : bool
	{
		foreach($arr as $el) 
			if($object === $el) return true;
		
		return false;
	}
	
	protected function removePlayer(array $arr, Player $player) : array
	{
		$newarr = array();
		
		foreach($arr as $p)
			if($p->getName() !== $player->getName()) array_push($newarr, $p);
			
		return $newarr;
	}
	
	protected function broadcast(string $message)
	{
		foreach($this->players as $p) $p->sendMessage($message);
	}
	
	protected function broadcastPopup(string $message)
	{
		foreach($this->players as $p) $p->sendPopup($message);
	}
	
	protected function broadcastTitle(string $line1, string $line2, Player $except = null)
	{
		foreach($this->players as $p) 
		{
			if($except !== $p) $p->addTitle($line1, $line2);
		}
	}
	
	protected function isOnline(Player $player) : bool
	{
		return !($this->plugin->getServer()->getPlayer($player->getName()) === null);
	}
	
	protected function broadcastAll(string $message)
	{
		$this->plugin->getServer()->broadcastMessage($this->plugin->getPrefix() . "§7(§d" . $this->getGameName() . "§7) §f" . $message);
	}
}

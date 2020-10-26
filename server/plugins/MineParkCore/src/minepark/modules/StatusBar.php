<?php
namespace minepark\modules;

use minepark\utils\CallbackTask;
use minepark\Core;

class StatusBar
{
	public $tmsg;
	
	public function __construct()
	{
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "t"]), 20);
		$this->tmsg = 0;
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}

	public function t()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p)
		{
			if($p->bar != null) $p->sendPopup($p->bar);
		}
		//text with information into chat 
		$this->tmsg++;
		if($this->tmsg == 60 * 3)
		{
			if(file_exists("strings/messages.txt")) {
				$lines = explode("\r\n", file_get_contents("strings/messages.txt"));
				$line = $lines[mt_rand(0, count($lines)-1)];
				$f = "§7[§9i§7] §f".$line;
				foreach($this->getCore()->getServer()->getOnlinePlayers() as $p)
				{
					if($p->auth) $p->sendMessage($f);
				}
			}
			$this->tmsg = 0;
		}
	}
}
?>
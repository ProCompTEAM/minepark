<?php
namespace minepark\components;

use minepark\Core;
use minepark\utils\CallbackTask;
use minepark\components\base\Component;

class StatusBar extends Component
{
	public $tmsg;
	
	public function __construct()
	{
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "timer"]), 20);
		$this->tmsg = 0;
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}

	public function timer()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
			if($p->getStatesMap()->bar != null) {
				$p->sendTip($p->getStatesMap()->bar);
			}
		}

		/* 
		 /// TODO: rewrite in #73

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
					if($p->getStatesMap()->auth) $p->sendMessage($f);
				}
			}
			$this->tmsg = 0;
		}*/
	}
}
?>
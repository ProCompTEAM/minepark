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
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
			if($player->getStatesMap()->bar != null) {
				$player->sendTip($player->getStatesMap()->bar);
			}
		}
	}
}
?>
<?php
namespace minepark\modules;

use minepark\Api;
use minepark\utils\CallbackTask;
use minepark\Core;

class PayDay
{
	
	public function __construct()
	{
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "t"]), 20 * 600);
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function t()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
			$salary = 0; 
			$special = 0;
			
			switch($p->org)
			{
				case 1: $salary = 200; break;
				case 2: $salary = 600; break;
				case 3: $salary = 500; break;
				case 4: $salary = 300; break;
				case 5: $salary = 400; break;
				case 6: $salary = 2000; break;
				case 7: $salary = 500; break;
			}
			
			if(!$p->hasPlayedBefore() and $p->isnew) {
				$special += 200;
			}

			if($p->org == 0) {
				$special += 100;
			}

			if($this->getCore()->getApi()->existsAttr($p, Api::ATTRIBUTE_BOSS)) {
				$salary *= 2;
			}

			$summ = ($salary + $special);

			$f = "§7----=====§eВРЕМЯ ЗАРПЛАТЫ§7=====----";
			$f .= "\n §3> §fЗаработано: §2" . $salary;
			$f .= "\n §3> §fПособие: §2" . $special;
			$f .= "\n§8- - - -== -==- ==- - - -";
			$f .= "\n §3☛ §fИтого: §2" . $summ;

			if($summ > 0) {
				$this->getCore()->getBank()->givePlayerMoney($p, $summ, false);
			}

			if($summ < 0) {
				$this->getCore()->getBank()->takePlayerMoney($p, $summ);
			}

			if($p->auth) {
				$p->sendMessage($f);
			}
		}
	}
}
?>
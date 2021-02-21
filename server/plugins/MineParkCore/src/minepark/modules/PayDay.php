<?php
namespace minepark\modules;

use minepark\Api;
use minepark\Core;
use minepark\Providers;
use minepark\utils\CallbackTask;
use minepark\common\player\MineParkPlayer;
use minepark\modules\organisations\Organisations;

class PayDay
{
	
	public function __construct()
	{
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "calcAndShow"]), 20 * 600);
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function calcAndShow()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
			$player = MineParkPlayer::cast($player);

			$salary = $this->getSalaryValue($player); 
			$special = 0;

			if(!$player->getStatesMap()->isNew) {
				$special += 200;
			}

			if($player->getProfile()->organisation == Organisations::NO_WORK) {
				$special += 100;
			}

			if($this->getCore()->getApi()->existsAttr($player, Api::ATTRIBUTE_BOSS)) {
				$salary *= 2;
			}

			$summ = ($salary + $special);

			if($summ > 0) {
				Providers::getBankingProvider()->giveDebit($player, $summ);
			}

			if($summ < 0) {
				Providers::getBankingProvider()->reduceDebit($player, $summ);
			}

			$this->sendForm($player, $salary, $special, $summ);
		}
	}

	private function sendForm(MineParkPlayer $player, int $salary, int $special, int $summ) 
	{
		$form = "§7----=====§eВРЕМЯ ЗАРПЛАТЫ§7=====----";
		$form .= "\n §3> §fЗаработано: §2" . $salary;
		$form .= "\n §3> §fПособие: §2" . $special;
		$form .= "\n§8- - - -== -==- ==- - - -";
		$form .= "\n §3☛ §fИтого: §2" . $summ;

		if($player->getStatesMap()->auth) {
			$player->sendMessage($form);
		}
	}

	private function getSalaryValue(MineParkPlayer $player) : int
	{
		switch($player->getProfile()->organisation)
		{
			case Organisations::TAXI_WORK: 
				return 200;
			case Organisations::DOCTOR_WORK: 
				return 600;
			case Organisations::LAWYER_WORK: 
				return 500;
			case Organisations::SECURITY_WORK: 
				return 300;
			case Organisations::SELLER_WORK: 
				return 400;
			case Organisations::GOVERNMENT_WORK:
				return 2000;
			case Organisations::EMERGENCY_WORK: 
				return 500;
		}

		return 0;
	}
}
?>
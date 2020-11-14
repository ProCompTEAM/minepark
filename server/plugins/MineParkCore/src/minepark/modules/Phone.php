<?php
namespace minepark\modules;

use minepark\Core;
use minepark\Mapper;
use pocketmine\Player;
use pocketmine\math\Vector3;

use pocketmine\utils\Config;
use minepark\utils\CallbackTask;

class Phone
{
	public const MAX_STREAM_DISTANCE = 200;

	public function __construct()
	{
		$this->c = new Config($this->getCore()->getTargetDirectory() . "phone.json", Config::JSON);
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "update"]), 20 * 60);
	}
	
	public function getCore() : Core
	{
		return Core::getActive();
	}

	public function init(Player $p) : int
	{
		$c = $this->c;
		if($this->getNumber($p) and is_numeric($this->getNumber($p))) 
			return $this->getNumber($p);
		else {
			$users = $c->getNested("numbers");
			$n = 10001; 
			
			if($users != null) {
				$n = 10001 + count($users);
			}

			$c->setNested("numbers.".$n, strtolower($p->getName()));
			$c->save();

			return $n;
		}
	}
	
	public function getNumber(Player $p) : int
	{
		$c = $this->c;
		$list = $c->getNested("numbers");

		if($list == null) {
			return false;
		}

		foreach($list as $num => $i) {
			$name = $c->getNested("numbers.".$num);

			if($name == strtolower($p->getName())) {
				return $num;
			}
		}

		return false;
	}
	
	public function getPlayer($number, $nickOnly = false) : ?Player
	{
		$c = $this->c;
		$nick = $c->getNested("numbers.".$number);

		if($nick) {
			return $nickOnly ? $nick : $this->getCore()->getServer()->getPlayer($nick);
		}
		
		return null;
	}
	
	public function hasStream(Vector3 $pos) : bool
	{
		return $this->getCore()->getMapper()->hasNearPointWithType($pos, self::MAX_STREAM_DISTANCE, Mapper::PHONESTREAM_POINT_GROUP);
	}

	public function handleInCall(Player $player, string $message) 
	{
		$number = $this->getNumber($player);

		$player->phoneRcv->sendMessage("§9✆ §e$number §6: §a".$message);
		$player->sendMessage("§9✆ §5$number §6: §2".$message);
	}

	public function breakCall(Player $player) 
	{
		$player->phoneRcv->sendMessage("§7[на экране мобильного] §6: §6Связь прервалась!");
		$player->phoneRcv->phoneRcv = null;
	}
	
	public function sendMessage($number, $text, $title) : bool
	{
		$p = $this->getPlayer($number);

		if($p !== null and $this->hasStream($p->getPosition())) {
			$p->sendMessage("§b[➪] SMS > отправитель: §e".$title);
			$p->sendMessage("§b[➪] ".$text);
			return true;
		}
		
		return false;
	}
	
	public function cmd($player, $cmds)
	{
		$this->getCore()->getChatter()->send($player, "§8(§dв руках телефон§8)", "§d : ", 10);
		if(!isset($cmds[1])) {
			$t = "";
			$t .= "§9☏ Позвонить: §e/c <номер телефона>\n";
			$t .= "§9☏ Служба Охраны: §e/c 02\n";
			$t .= "§9☏ Мед. помощь: §e/c 03\n";
			$t .= "§9☏ Сообщения: §e/sms <н.телефона> <текст>\n";
			$t .= "§1> Цены: §aСМС 20р, Звонок 20р минута\n";
			$t .= "§1> Ваш телефонный номер: §3".$this->getNumber($player);
			$player->sendWindowMessage($t, "§9❖======*Смартфон*=======❖");
		}
		else
		{
			$number = $cmds[1];
			if($number == "02" or $number == "03")
			{
				$oid = 4; if($number == "03") $oid = 2;
				$ps = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 15);
				if(count($ps) < 0) $player->sendMessage("§cОшибка выполнения вызова!");
				else
				{
					foreach($this->getCore()->getServer()->getOnlinePlayers() as $p)
					{
						if($p->getProfile()->organisation == $oid) 
						{
							if(count($ps) == 0) $p->sendMessage("§6[➪] !!! <=== ЭКСТРЕННЫЙ ВЫЗОВ (ПОЗВОНИТЕ) ===>");
							else $p->sendMessage("§d[➪] !!! <=== ЭКСТРЕННЫЙ ВЫЗОВ (ОТПРАВЛЯЙТЕСЬ) ===>");
							$p->sendMessage("§9[➪] !!! Номер вызова: §e".$this->getNumber($player));
							$p->sendMessage("§9[➪] !!! Зарегистрирован на: §e".$player->getProfile()->fullName);
							$p->sendMessage("§9[➪] !!! Адреса проживания: §3".implode(", ",$player->property));
							if(count($ps) == 0) $p->sendMessage("§c[➪] Место происшествия не определено");
							else $p->sendMessage("§6[➪] !!! МЕСТО ПРОИСШЕСТВИЯ: §7/gps §e".$ps[0]);
						}
					}
					$player->sendMessage("§6[➪] Вы вызвали службу экстренной помощи!");
					$player->sendMessage("§6[➪] Сотрудники прибудут в течении 10 минут!");
					$player->sendMessage("§6[➪] Ваши личные данные переданы для выяснения обстоятельств!");
					$player->sendMessage("§6[➪] Пожалуйста оставайтесь на этом же месте!");
				}
			}
			elseif($number == "action")
			{
				if($cmds[0] == "sms") return;
				if($player->phoneReq != null) {
					foreach(array($player->phoneReq, $player) as $p) {
						$p->sendMessage("§7[на экране мобильного] §6: §aдиалог с ".$this->getNumber($player->phoneReq).".."); 
						$p->sendMessage("§7[на экране мобильного] §6: §eтариф 20р минута!");
						$player->sendMessage("§b[➪] Завершить вызов: §6/c action");
					}
					$player->phoneRcv = $player->phoneReq;
					$player->phoneRcv->phoneRcv = $player;
					$player->phoneReq = null; $player->phoneRcv->phoneReq = null;
				}
				elseif($player->phoneRcv != null) 
				{
					foreach(array($player->phoneRcv, $player) as $p) {
						$p->sendMessage("§7[на экране мобильного] §6: §6Вызов завершен!");
						$p->phoneRcv = null;
					}
				}
				else $player->sendMessage("§7[на экране мобильного] §6: §5телефон перезагружен!"); 
			}
			else {
				if($this->hasStream($player->getPosition())) 
				{
					$mynumber = $this->getNumber($player);
					$player2 = $this->getPlayer($number);
					if($player2 !== null and $this->hasStream($player2->getPosition()))
					{
						if($cmds[0] == "c") 
						{
							$player->sendMessage("§b[➪] ..♪гудки♪.. ..♪гудки♪.. ..♪гудки♪..");
							if($number == $mynumber or !is_numeric($number)) 
								$player->sendMessage("§b[➪] Проверьте номер, который вы набрали! ..♪гудки♪..");
							elseif($player2->phoneRcv == null) {
								$this->getCore()->getChatter()->send($player2, "♪♪♪Звонит телефон в кармане♪♪♪", "§d : ", 10);
								$player2->sendMessage("§b[➪] Вам звонит абонент с номером §e".$mynumber.".");
								$player2->sendMessage("§b[➪] Ответить на этот звонок: §6/c action");
								$player2->phoneReq = $player;
							}
							else $player->sendMessage("§b[➪] Линия занята! ..♪короткие гудки♪..");
						}
						else 
						{
							if($this->getCore()->getBank()->takePlayerMoney($player, 20)) 
							{
								$this->getCore()->getChatter()->send($player2, "♪♪Из кармана слышен звук СМС-ки♪♪", "§d : ", 10);
								$sms = $this->sendMessage($number, $this->getCore()->getApi()->getFromArray($cmds, 2), $mynumber);
								if(!$sms) $player->sendMessage("§7[на экране мобильного] §6: §cОшибка при передачи сообщения!");
								else $player->sendMessage("§7[на экране мобильного] §6: §aСообщение доставлено получателю!");
							}
							else $player->sendMessage("§7[на экране мобильного] §6: §cНедостаточно средств, пополните счет!");
						}
					}
					else $player->sendMessage("§b[➪] Абонент вне зоны действия сети! ..♪гудки♪..");
				}
				else $player->sendMessage("§7[на экране мобильного] §6: §c-нет сети рядом-");
			}
		}
	}
	
	public function update()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p)
		{
			if($p->phoneRcv != null)
			{
				if($this->hasStream($p->phoneRcv->getPosition()))
				{
					if(!$this->getCore()->getBank()->takePlayerMoney($p, 20)) 
					{
						$p->sendMessage("§b[➪] Для продолжения разговора у вас недостаточно средств! ..♪гудки♪..");
						$p->sendMessage("§7[на экране мобильного] §6: §6Связь прервалась!");
						$p->phoneRcv->sendMessage("§7[на экране мобильного] §6: §6Связь прервалась!");
						$p->phoneRcv->phoneRcv = null; $p->phoneRcv = null; 
					}
				}
				else 
				{
					$p->sendMessage("§b[➪] Абонент вне зоны действия сети! ..♪гудки♪..");
					$p->sendMessage("§7[на экране мобильного] §6: §6Связь прервалась!");
					$p->phoneRcv->sendMessage("§7[на экране мобильного] §6: §6Связь прервалась!");
					$p->phoneRcv->phoneRcv = null; $p->phoneRcv = null; 
				}
			}
		}
	}
}
?>
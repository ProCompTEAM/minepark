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
		return $this->getCore()->getMapper()->hasNearPointWithType($pos, self::MAX_STREAM_DISTANCE, Mapper::POINT_GROUP_STREAM);
	}

	public function handleInCall(Player $player, string $message) 
	{
		$number = $this->getNumber($player);

		$player->phoneRcv->sendMessage("§9✆ §e$number §6: §a".$message);
		$player->sendMessage("§9✆ §5$number §6: §2".$message);
	}

	public function breakCall(Player $player) 
	{
		$player->phoneRcv->sendMessage("PhoneSmsErrorNet");
		$player->phoneRcv->phoneRcv = null;
	}
	
	public function sendMessage($number, $text, $title) : bool
	{
		$p = $this->getPlayer($number);

		if($p !== null and $this->hasStream($p->getPosition())) {
			$p->sendLocalizedMessage("{PhoneSend}".$title);
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
				if(count($ps) < 0) $player->sendMessage("PhoneErrorCall");
				else
				{
					foreach($this->getCore()->getServer()->getOnlinePlayers() as $p)
					{
						if($p->getProfile()->organisation == $oid) 
						{
							if(count($ps) == 0) $p->sendMessage("PhoneEvent1");
							else $p->sendMessage("PhoneEvent2");
							$p->sendLocalizedMessage("{PhoneEvent3}".$this->getNumber($player));
							$p->sendLocalizedMessage("{PhoneEvent4}".$player->getProfile()->fullName);
							$p->sendLocalizedMessage("{PhoneEvent5}".implode(", ",$player->property));
							if(count($ps) == 0) $p->sendMessage("PhoneEvent6");
							else $p->sendLocalizedMessage("{PhoneEvent7}".$ps[0]);
						}
					}
					$player->sendMessage("PhoneEventCallHelp1");
					$player->sendMessage("PhoneEventCallHelp2");
					$player->sendMessage("PhoneEventCallHelp3");
					$player->sendMessage("PhoneEventCallHelp4");
				}
			}
			elseif($number == "action")
			{
				if($cmds[0] == "sms") return;
				if($player->phoneReq != null) {
					foreach(array($player->phoneReq, $player) as $p) {
						$p->sendLocalizedMessage("{PhoneCall1}".$this->getNumber($player->phoneReq).".."); 
						$p->sendMessage("PhoneCall2");
						$player->sendMessage("PhoneCall3");
					}
					$player->phoneRcv = $player->phoneReq;
					$player->phoneRcv->phoneRcv = $player;
					$player->phoneReq = null; $player->phoneRcv->phoneReq = null;
				}
				elseif($player->phoneRcv != null) 
				{
					foreach(array($player->phoneRcv, $player) as $p) {
						$p->sendMessage("PhoneCallEnd");
						$p->phoneRcv = null;
					}
				}
				else $player->sendMessage("PhoneCallReload"); 
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
							$player->sendMessage("PhoneBeeps");
							if($number == $mynumber or !is_numeric($number)) 
								$player->sendMessage("PhoneCheckNum");
							elseif($player2->phoneRcv == null) {
								$this->getCore()->getChatter()->send($player2, "{PhoneCallingBeep}", "§d : ", 10);
								$player2->sendLocalizedMessage("{PhoneCalling1}".$mynumber.".");
								$player2->sendMessage("PhoneCalling2");
								$player2->phoneReq = $player;
							}
							else $player->sendMessage("PhoneCalling3");
						}
						else 
						{
							if($this->getCore()->getBank()->takePlayerMoney($player, 20)) 
							{
								$this->getCore()->getChatter()->send($player2, "{PhoneSmsBeep}", "§d : ", 10);
								$sms = $this->sendMessage($number, $this->getCore()->getApi()->getFromArray($cmds, 2), $mynumber);
								if(!$sms) $player->sendMessage("PhoneSmsError");
								else $player->sendMessage("PhoneSmsSucces");
							}
							else $player->sendMessage("PhoneSmsNoMoney");
						}
					}
					else $player->sendMessage("PhoneSmsNoNet");
				}
				else $player->sendMessage("PhoneSmsNoNet2");
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
						$p->sendMessage("PhoneSmsContinueNoMoney");
						$p->sendMessage("PhoneSmsErrorNet");
						$p->phoneRcv->sendMessage("PhoneSmsErrorNet");
						$p->phoneRcv->phoneRcv = null; $p->phoneRcv = null; 
					}
				}
				else 
				{
					$p->sendMessage("PhoneSmsNoNet");
					$p->sendMessage("PhoneSmsErrorNet");
					$p->phoneRcv->sendMessage("PhoneSmsErrorNet");
					$p->phoneRcv->phoneRcv = null; $p->phoneRcv = null; 
				}
			}
		}
	}
}
?>
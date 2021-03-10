<?php
namespace minepark\components;

use minepark\Core;
use minepark\Mapper;
use minepark\Providers;

use pocketmine\math\Vector3;

use minepark\utils\CallbackTask;
use minepark\components\base\Component;
use minepark\providers\data\PhonesSource;
use minepark\common\player\MineParkPlayer;
use minepark\components\organisations\Organisations;

class Phone extends Component
{
	public const MAX_STREAM_DISTANCE = 200;

	public const EMERGENCY_NUMBER1 = "02";
	public const EMERGENCY_NUMBER2 = "03";

	private $source;

	public function __construct()
	{
		$this->source = $this->getCore()->getMDC()->getSource(PhonesSource::ROUTE);
		$this->getCore()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "takeFee"]), 20 * 60);
	}
	
	public function getNumber(MineParkPlayer $player) : int
	{
		return $this->getSource()->getNumberForUser($player->getName());
	}
	
	public function getPlayer(int $number, bool $nameOnly = false) : ?MineParkPlayer
	{
		$name = $this->getSource()->getUserNameByNumber($number);

		if(strlen($name) > 0) {
			return $nameOnly ? $name : $this->getCore()->getServer()->getPlayer($name);
		}
		
		return null;
	}
	
	public function hasStream(Vector3 $pos) : bool
	{
		return $this->getCore()->getMapper()->hasNearPointWithType($pos, self::MAX_STREAM_DISTANCE, Mapper::POINT_GROUP_STREAM);
	}

	public function handleInCall(MineParkPlayer $player, string $message) 
	{
		$number = $this->getNumber($player);

		$player->getStatesMap()->phoneRcv->sendMessage("§9✆ §e$number §6: §a".$message);
		$player->sendMessage("§9✆ §5$number §6: §2".$message);
	}

	public function breakCall(MineParkPlayer $player) 
	{
		$player->getStatesMap()->phoneRcv->sendMessage("PhoneSmsErrorNet");
		$player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null;
	}
	
	public function sendMessage(int $number, string $text, string $title) : bool
	{
		$player = $this->getPlayer($number);

		if($player !== null and $this->hasStream($player->getPosition())) {
			$player->sendLocalizedMessage("{PhoneSend}" . $title);
			$player->sendMessage("§b[➪] " . $text);
			return true;
		}
		
		return false;
	}
	
	public function cmd(MineParkPlayer $player, array $commandArgs)
	{
		$this->getCore()->getChatter()->send($player, "§8(§dв руках телефон§8)", "§d : ", 10);

		if(!isset($commandArgs[1])) {
			$this->sendDisplayMessages($player);
		} else {
			$number = $commandArgs[1];

			if (!is_numeric($number)) {
				return $player->sendMessage("PhoneCheckNum");
			}

			if($number == self::EMERGENCY_NUMBER1 or $number == self::EMERGENCY_NUMBER2) {
				$organisationId = Organisations::SECURITY_WORK;

				if($number == self::EMERGENCY_NUMBER2) {
					$organisationId = Organisations::DOCTOR_WORK;
				}

				$this->emergencyCall($player, $organisationId);
			}
			elseif($number == "action") {
				if($commandArgs[0] == "sms") {
					return;
				}

				$this->acceptNewCallOrCancel($player);
			} else {
				if($this->hasStream($player->getPosition())) {
					$targetPlayer = $this->getPlayer($number);
					if($targetPlayer !== null and $this->hasStream($targetPlayer->getPosition())) {
						$this->makeCallOrSendSMS($player, $targetPlayer, $commandArgs, $number);
					}
					else {
						$player->sendMessage("PhoneSmsNoNet");
					}
				}
				else {
					$player->sendMessage("PhoneSmsNoNet2"); 
				}
			}
		}
	}
	
	public function takeFee()
	{
		foreach($this->getCore()->getServer()->getOnlinePlayers() as $player) {
			$player = MineParkPlayer::cast($player);
			if($player->getStatesMap()->phoneRcv != null) {
				if($this->hasStream($player->getStatesMap()->phoneRcv->getPosition())) {
					if(!Providers::getBankingProvider()->takePlayerMoney($player, 20)) {
						$player->sendMessage("PhoneSmsContinueNoMoney");
						$player->sendMessage("PhoneSmsErrorNet");

						$player->getStatesMap()->phoneRcv->sendMessage("PhoneSmsErrorNet");

						$player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null; 
						$player->getStatesMap()->phoneRcv = null; 
					}
				}
				else {
					$player->sendMessage("PhoneSmsNoNet");
					$player->sendMessage("PhoneSmsErrorNet");

					$player->getStatesMap()->phoneRcv->sendMessage("PhoneSmsErrorNet");

					$player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = null; 
					$player->getStatesMap()->phoneRcv = null; 
				}
			}
		}
	}

	private function getSource() : PhonesSource
	{
		return $this->source;
	}

	private function sendDisplayMessages(MineParkPlayer $player)
	{
		$message  = "§9☏ Позвонить: §e/c <номер телефона>\n";
		$message .= "§9☏ Служба Охраны: §e/c 02\n";
		$message .= "§9☏ Мед. помощь: §e/c 03\n";
		$message .= "§9☏ Сообщения: §e/sms <н.телефона> <текст>\n";
		$message .= "§1> Цены: §aСМС 20р, Звонок 20р минута\n";
		$message .= "§1> Ваш телефонный номер: §3" . $player->getProfile()->phoneNumber;

		$player->sendWindowMessage($message, "§9❖======*Смартфон*=======❖");
	}

	private function acceptNewCallOrCancel(MineParkPlayer $player)
	{
		if($player->getStatesMap()->phoneReq != null) {
			foreach(array($player->getStatesMap()->phoneReq, $player) as $p) {
				$p->sendLocalizedMessage("{PhoneCall1}".$this->getNumber($player->getStatesMap()->phoneReq) . ".."); 
				$p->sendMessage("PhoneCall2");
				$player->sendMessage("PhoneCall3");
			}

			$player->getStatesMap()->phoneRcv = $player->getStatesMap()->phoneReq;
			$player->getStatesMap()->phoneRcv->getStatesMap()->phoneRcv = $player;

			$player->getStatesMap()->phoneReq = null; 
			$player->getStatesMap()->phoneRcv->getStatesMap()->phoneReq = null;
		} elseif($player->getStatesMap()->phoneRcv != null) {
			foreach(array($player->getStatesMap()->phoneRcv, $player) as $p) {
				$p->sendMessage("PhoneCallEnd");
				$p->getStatesMap()->phoneRcv = null;
			}
		} else {
			$player->sendMessage("PhoneCallReload"); 
		}
	}

	private function emergencyCall(MineParkPlayer $player, int $organisationId)
	{
		$streams = $this->getCore()->getMapper()->getNearPoints($player->getPosition(), 15);

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $onlinePlayer) {
			$onlinePlayer = MineParkPlayer::cast($onlinePlayer);
			if($onlinePlayer->getProfile()->organisation == $organisationId) {
				if(count($streams) == 0) {
					$onlinePlayer->sendMessage("PhoneEvent1");
				} else {
					$onlinePlayer->sendMessage("PhoneEvent2");
				}

				$onlinePlayer->sendLocalizedMessage("{PhoneEvent3}" . $this->getNumber($player));
				$onlinePlayer->sendLocalizedMessage("{PhoneEvent4}" . $player->getProfile()->fullName);
				$onlinePlayer->sendLocalizedMessage("{PhoneEvent5}" . implode(", ",$player->property));
	
				if(count($streams) == 0) {
					$onlinePlayer->sendMessage("PhoneEvent6");
				} else {
					$onlinePlayer->sendLocalizedMessage("{PhoneEvent7}" . $streams[0]);
				}
			}
		}

		$player->sendMessage("PhoneEventCallHelp1");
		$player->sendMessage("PhoneEventCallHelp2");
		$player->sendMessage("PhoneEventCallHelp3");
		$player->sendMessage("PhoneEventCallHelp4");
	}

	private function makeCallOrSendSMS(MineParkPlayer $player, MineParkPlayer $targetPlayer, array $commandArgs, int $number)
	{
		$myNumber = $this->getNumber($player);

		if($commandArgs[0] == "c") {
			$player->sendMessage("PhoneBeeps");

			if($number == $myNumber) {
				$player->sendMessage("PhoneCheckNum");
			} elseif($targetPlayer->getStatesMap()->phoneRcv == null) {
				$this->getCore()->getChatter()->send($targetPlayer, "{PhoneCallingBeep}", "§d : ", 10);

				$targetPlayer->sendLocalizedMessage("{PhoneCalling1}".$myNumber.".");
				$targetPlayer->sendMessage("PhoneCalling2");

				$targetPlayer->getStatesMap()->phoneReq = $player;
			} else {
				$player->sendMessage("PhoneCalling3");
			}
		} else {
			if(Providers::getBankingProvider()->takePlayerMoney($player, 20)) {
				$this->getCore()->getChatter()->send($targetPlayer, "{PhoneSmsBeep}", "§d : ", 10);

				$sms = $this->sendMessage($number, $this->getCore()->getApi()->getFromArray($commandArgs, 2), $myNumber);

				if(!$sms) {
					$player->sendMessage("PhoneSmsError");
				} else {
					$player->sendMessage("PhoneSmsSucces");
				}
			}
			else {
				$player->sendMessage("PhoneSmsNoMoney");
			}
		}
	}
}
?>
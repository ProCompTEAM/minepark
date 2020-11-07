<?php
namespace minepark;

use pocketmine\Player;

class Api
{
	public const ATTRIBUTE_HAS_PASSPORT = 'P';
	public const ATTRIBUTE_ARRESTED = 'A';
	public const ATTRIBUTE_WANTED = 'W';
	public const ATTRIBUTE_BOSS = 'B';

	public function getCore() : Core
	{
		return Core::getActive();
	}
	
	public function getName() : string
	{
		return "MinePark";
	}
	
	public function getColor($textspace) : string
	{
		return "§" . $textspace;
	}
	
	public function getPrefix($pId, $withColor = false) : string
	{
		$form = null;

		switch($pId)
		{
            case 1: $form = "§0Неизвестный" ; break;
            case 2: $form = "§5Оператор" ; break;
            case 3: $form = "§6Заключенный" ; break;
            case 4: $form = "§2Рабочий" ; break;
            case 5: $form = "§cДоктор" ; break;
            case 6: $form = "§aГос. Служащий" ; break;
            case 7: $form = "§3Служба Охраны" ; break;
            case 8: $form = "§dПродавец" ; break;
            case 9: $form = "§eЮрист" ; break;
            case 10: $form = "§4Служба Спасения"; break;
			default: return "§f"; break;
		}

		$withColor ? $form : substr($form, 2);
	}
	
	public function getRegionPlayers(float $x1, float $y1, float $z1, int $rad) : array
	{
		$plist = array();

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $sender) {
			$p_x = $sender->getX();
			$p_y = $sender->getY();
			$p_z = $sender->getZ();

			$x = $x1 - $p_x;
			$z = $z1 - $p_z;
			$y = $y1 - $p_y;

			$x = floor($x);
			$z = floor($z);
			$y = floor($y);

			if($x < $rad and $z < $rad and $x > $rad*-1 and $z > $rad*-1 and $y < $rad and $y > $rad*-1) {
				array_push($plist, $sender);
			}
		}

		return $plist;
	}
	
	public function localBroadcast(float $x1, float $y1, float $z1, float $rad, string $text)
	{
		foreach($this->getRegionPlayers($x1, $y1, $z1, $rad) as $p) {
			$p->sendMessage($text);
		}
	}
	
	public function getFromArray(array $array, int $min, string $split_str = " ") : string
	{
	   $val = -1;  
	   $str = "";

	   foreach($array as $a) {
			$val = $val + 1;

			if($val > $min) {
				$str .= $split_str.$a;
			} elseif($val == $min) {
				$str .= $a;
			}
	   }

	   return $str;
	}
	
	public function existsAttr(Player $player, string $key) : bool
	{
		return (preg_match('/'.strtoupper($key).'/', $player->temp));
	}
	
	public function changeAttr(Player $player, string $key, bool $status = true)
	{
		if(!$status) {
			$player->temp = str_replace(strtoupper($key), '', $player->temp);
		} elseif(!$this->existsAttr($player, strtoupper($key))) {
			$player->temp .= strtoupper($key);
		}

		$this->getCore()->getInitializer()->updatePlayerSaves($player);
	}
	
	public function arest(Player $player)
	{
		$this->getCore()->getMapper()->teleportPoint($player, "КПЗ");
		$this->getCore()->getApi()->changeAttr($player, self::ATTRIBUTE_ARRESTED);
		$this->getCore()->getApi()->changeAttr($player, self::ATTRIBUTE_WANTED, false);

		$player->bar = "§6ВЫ АРЕСТОВАНЫ!";

		$player->setImmobile(false);
	}
	
	public function scndr(string $dir, int $sort = 0) : array
	{
		$list = scandir($dir, $sort);

		if (!$list) {
			return false;
		}

		if ($sort == 0) {
			unset($list[0],$list[1]);
		} else {
			unset($list[count($list)-1], $list[count($list)-1]);
		}

		return $list;
	}

	public function interval(int $value, int $from, int $to)
	{
		return ($value >= min($from, $to) and $value <= max($from, $to));
	}

	public function getAdministration(bool $namesOnly = false) : array
	{
		$list = [];

		foreach($this->getCore()->getServer()->getOnlinePlayers() as $p) {
			if ($p->hasPermission(Permission::ADMINISTRATOR)) {
				$namesOnly ? array_push($list, $p->getName()) : array_push($list, $p);
			}
		}

		return $list;
	}
	
	public function sendToMessagesLog(string $prefix, string $message)
	{
		file_put_contents(Core::MESSAGES_LOG_FILE, (PHP_EOL . "(" . $prefix . ") - " . $message), FILE_APPEND);
	}
}
?>
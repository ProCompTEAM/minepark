<?php
namespace SmartRealty;

use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\Player;

use pocketmine\tile\Sign;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;

class Property
{
	public const MINIMUM_PREMIUM_PROPERTY_PRICE = 1500;
	public const MINIMUM_PREMIUM_PROPERTY_RENT_DAYS = 7;

	public $main;
	public $c;
	
	public function __construct($mainclass)
	{
		$this->main = $mainclass;
		
		/*foreach($this->getAreas() as $area)
		{
			$c = new Config($this->main->getDirectory()."property/".$area.".json", Config::JSON);
			foreach($c->getAll(true) as $name)
			{
				$c->setNested("$name.owners", false);
			}
			$c->save();
		}*/// THIS CODE CLEAR ALL PROPERTY (REMOVE OWNERS)
	}
	
	public function command($player, $args)
	{
		$maincmd = null; 
		
		if(isset($args[0])) {
			$maincmd = $args[0];
		}
		
		if(($maincmd == "add" or $maincmd == "setarea" or  $maincmd == "op") and !$player->getProfile()->realtor) {
			$player->sendMessage("§cАдминистратор не позволяет вам регистрировать недвижимость!"); 
			return;
		}
		
		switch($maincmd)
		{	
			case 'op':
			//op <nickname> 
			if(count($args) < 2) { 
				$player->sendMessage("§c/realt op <ник игрока>");
				return;
			}
			
			$p = $this->main->getServer()->getPlayer($args[1]);
			if($p !== null)
			{
				$p->getProfile()->realtor = true;
				//TODO: save player profile
				
				$this->main->getServer()->broadcastMessage("§8" . $player->getName() .
					" выдал временные права на реализацию недвижимости для " . $p->getName());
			}
			break;
		
			case 'pos':
			//real pos (1) > pos (2)
			if($player->propPos1 == null) {
				$player->propPos1 = $player->getPosition();
				$player->sendMessage("§eТочка #1 успешно установлена!");
			} else {
				$player->propPos2 = $player->getPosition();
				$player->sendMessage("§eТочка #2 успешно установлена!");
				$player->sendMessage("§dСоздать регион помещения: /realt add <имя> <цена за сутки> <высота(блоков до потолка)>!");
			}
			break;
			
			case 'setarea':
			//setarea <name> 
			if(count($args) < 2) { 
				$player->sendMessage("§c/realt setarea <имя микрорайона>");
				return;
			}
			if($player->propPos1 == null or $player->propPos2 == null) {
				$player->sendMessage("§cНеобходимо установить точки: /realt pos");
				return; 
			}
			$this->setArea($player->propPos1, $player->propPos2, $args[1]);
			$player->propPos1 = null; $player->propPos2 = null;
			break;
			
			case 'add':
			//add <name> <price> <height of block's>
			if(count($args) < 4) { 
				$player->sendMessage("§c<имя> <цена за сутки> <высота(блоков до потолка)>");
				return; 
			}
			
			if($player->propPos1 == null or $player->propPos2 == null) {
				$player->sendMessage("§cНеобходимо установить точки: /realt pos");
				return; 
			}
			
			$c = $this->getConfig($player->getPosition());
			if($c === null) {
				$player->sendMessage("§cНеобходимо создать микрорайон, в котором будет находиться недвижимость");
				return;
			}
			
			$name = $args[1];
			$c->setNested("$name.price",$args[2]);
			$c->setNested("$name.pos1.x", floor($player->propPos1->getX()));
			$c->setNested("$name.pos1.y", floor($player->propPos1->getY()));
			$c->setNested("$name.pos1.z", floor($player->propPos1->getZ()));
			$c->setNested("$name.pos2.x", floor($player->propPos2->getX()));
			$c->setNested("$name.pos2.y", floor($player->propPos2->getY()+$args[3]-1));
			$c->setNested("$name.pos2.z", floor($player->propPos2->getZ()));
			$c->setNested("$name.owners", false);
			$c->setNested("$name.rented", 0); 
			$c->setNested("$name.rented_days", 0); 
			$c->save();
			
			$player->sendMessage("§aПомещение добавлено в базу под наименованием §b$name");
			
			$player->propPos1 = null; 
			$player->propPos2 = null;
			
			$this->main->signev = true;
			break;
			
			case 'rent':
			//rent <name> <days>
			if(count($args) < 3) { 
				$player->sendMessage("§c/realt rent <имя> <сутки аренды>");
				return;
			}
			
			$name = $args[1];
			$days = $args[2];
			$c = $this->getConfig($player->getPosition());

			if ($c === null) {
				return $player->sendMessage("§cЗдесь не продается недвижимость!");
			}
			
			if(!$c->exists($name)) {
				$player->sendMessage("§cНедвижимости §6$name §cнет в этом микрорайоне!");
				return;
			}
			
			if(count($player->property) == 3 and !$player->isOp()) {
				$player->sendMessage("§6Дабы все жилье не скупили, государство ограничело количество квартир на человека до трех.");
				$player->sendMessage("§eВы можете дождаться окончание аренды одного из купленного жилья, а затем сюда вернуться!");
				return;
			}
			
			$this->checkRented($name);
			
			$price = $c->getNested("$name.price");

			if ($price > self::MINIMUM_PREMIUM_PROPERTY_PRICE and $days < self::MINIMUM_PREMIUM_PROPERTY_RENT_DAYS) {
				return $player->sendMessage("§cДанную недвижимость нельзя арендовать на менее 7 дней.");
			}
			
			if($c->getNested("$name.owners") != false) 
				$player->sendMessage("§cСожалеем, но эта недвижимость в данный момент уже кем-то арендована!");
			else 
			{
				if($this->main->economy->reduceMoney($player, $price * $days)) 
				{
					$c->setNested("$name.owners", strtolower($player->getName()));
					$c->setNested("$name.rented", time());
					$c->setNested("$name.rented_days", $days);
					$c->save();
					
					$player->sendMessage("§9Поздравляем! Вы арендовали это помещение на $days суток!");
					$this->updatePlayerProperty($player);
				}
				else $player->sendMessage("§cК сожалению, в данный момент вам не хватает денег для аренды!"
					. " Возможно, стоит попробовать арендовать жилье на меньшее количество суток.");
			}
			break;
		}
	}
	
	public function tap($event)
	{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		
		if($block instanceof Sign or $block instanceof SignPost or $block instanceof WallSign)
		{
			$c = $this->getConfig($player->getPosition());
			if($c === null)
			{
				if($player->hasPermission("realt.creator")) 
					$player->sendMessage("§8Внимание! Микрорайон на этом участке не обозначен!");
				return;
			}
			foreach($c->getAll(true) as $name) {
				$x = $c->getNested("$name.sign.x");
				$y = $c->getNested("$name.sign.y");
				$z = $c->getNested("$name.sign.z");
				if($x == floor($block->getX()) and $y == floor($block->getY()) 
					and $z == floor($block->getZ())) 
					{
						$this->checkRented($name);
						
						$price = $c->getNested("$name.price");
						$days = $c->getNested("$name.rented_days") - round((time() - $c->getNested("$name.rented")) / 86400);
						
						$f = "§f### §3Паспорт жилого объекта §a$name §f###\n";
						$f .= "§f=> Микрорайон/Регион: §3".$this->getArea($player->getPosition())."\n";
						if($c->getNested("$name.owners")) {
							if(!$player->isOp()) $f .= "§f=> Это помещение §6уже арендовано§f!\n";
							else $f .= "§f=> Квартирант: §c".$c->getNested("$name.owners")."\n";
							$f .= "§f=> До конца аренды осталось: §3$days суток\n";
						}
						else { 
							$f .= "§f=> Цена аренды за сутки: §3$price\n";
							$f .= "§f=> Арендовать помещение: §d/realt rent $name <количество суток>\n";
						}
						
						$f .= "§8Сняв на время помещение вы сможете строить внутри него, проживать и приглашать жить друзей.";
						
						$player->sendMessage($f);
					}
			}
		}
	}
	
	public function sign($event)
	{
		$lns = $event->getLines();
		if(($lns[0] == "[property]" or $lns[0] == "[realty]" or $lns[0] == "realt") and $event->getPlayer()->getProfile()->realtor) 
		{
			$player = $event->getPlayer();
			$name = $lns[1];
			$c = $this->getConfig($player->getPosition());
			
			if($c->exists($name)) 
			{
				$x = floor($event->getBlock()->getX());
				$y = floor($event->getBlock()->getY());
				$z = floor($event->getBlock()->getZ());
				
				$c->setNested("$name.sign.x",$x);
				$c->setNested("$name.sign.y",$y);
				$c->setNested("$name.sign.z",$z);
				$c->save();
				
				$event->setLine(0, "§fСДАЁТСЯ В АРЕНДУ"); 
				$event->setLine(1, " ");
				$event->setLine(2, "§3нажмите на табличку");
				$event->setLine(3, "§3чтобы арендовать");
				
				$player->sendMessage("§aЛинковка произведена!");
			}
			else $player->sendMessage("§cНеправильное имя!");
		}
	}
	
	public function block($event)
	{
		if($event->getPlayer()->isOp()) return;
		
		$block = $event->getBlock();
		
		if(($block instanceof WallSign or $block instanceof SignPost or $block instanceof Sign) and $event->getPlayer()->getProfile()->realtor) {
			$event->setCancelled(false);
			return;
		}
	
		$player = $event->getPlayer();
		$list = $player->property;
		$c = $this->getConfig($player->getPosition());
		
		if($c === null) $event->setCancelled();
		else
		{
			foreach($list as $name)
			{
				$x1 = $c->getNested("$name.pos1.x");
				$y1 = $c->getNested("$name.pos1.y");
				$z1 = $c->getNested("$name.pos1.z");
				$x2 = $c->getNested("$name.pos2.x");
				$y2 = $c->getNested("$name.pos2.y");
				$z2 = $c->getNested("$name.pos2.z");
				
				$x = floor($block->getX());
				$y = floor($block->getY());
				$z = floor($block->getZ());
				
				if($this->interval($x,$x1,$x2) and $this->interval($y,$y1,$y2) and $this->interval($z,$z1,$z2)) {
						$event->setCancelled(false);
						return;
				}
			}

			$event->setCancelled();
		}
	}
	
	public function interval($value, $from, $to)
	{
		$min = min($from, $to); $max = max($from, $to);
		if($value >= $min and $value <= $max) return true;
		else return false;
	}
	
	public function setArea(Position $pos1, Position $pos2, $name)
	{
		if(!file_exists($this->main->getDirectory()."property/"))
			mkdir($this->main->getDirectory()."property/");
		
		$form = $name."=".floor($pos1->getX()).",".floor($pos1->getY()).",".floor($pos1->getZ()).
		">>".floor($pos2->getX()).",".floor($pos2->getY()).",".floor($pos2->getZ());
		
		if(!file_exists($this->main->getDirectory()."property/list.txt"))
			file_put_contents($this->main->getDirectory()."property/list.txt",$form);
		else {
			file_put_contents($this->main->getDirectory()."property/list.txt",
				file_get_contents($this->main->getDirectory()."property/list.txt")." ".$form);
		}
		
		new Config($this->main->getDirectory()."property/".$name.".json", Config::JSON);
	}
	
	public function getAreas($namesOnly = true)
	{
		if(!file_exists($this->main->getDirectory()."property/"))
		{
			mkdir($this->main->getDirectory()."property/");
			file_put_contents($this->main->getDirectory()."property/list.txt","");
			return array();
		}
		else
		{
			if(!file_exists($this->main->getDirectory()."property/list.txt"))
			{
				file_put_contents($this->main->getDirectory()."property/list.txt","");
				return array();
			}
			else
			{
				$data = file_get_contents($this->main->getDirectory()."property/list.txt");
				if($data == "") return array();
				else {
					$data = explode(" ", $data);
					$result = array();
					foreach($data as $line)
					{
						//AreaName=0,0,0>>100,100,100
						$splittedLine = explode("=", $line);
						if (!isset($splittedLine[1])) {
							continue;
						}

						$name = $splittedLine[0];

						if($namesOnly) array_push($result, $name);
						else { 
							$part2 = $splittedLine[1];
							$poslist = explode(">>", $part2);
							$form = array($name);

							foreach($poslist as $posf)
							{
								$splittedPosition = explode(',', $posf);
								$x = $splittedPosition[0];
								$y = $splittedPosition[1];
								$z = $splittedPosition[2];

								array_push($form, new Vector3($x,$y,$z));
							}
							array_push($result, $form);
						}
					}
					return $result;
				}
			}
		}
	}
	
	public function getArea(Position $pos, $nameOnly = true)
	{
		$areas = $this->getAreas(false);
		foreach($areas as $area)
		{
			$name = $area[0]; $pos1 = $area[1]; $pos2 = $area[2];
			if($this->interval($pos->getX(),$pos1->getX(),$pos2->getX())
					and $this->interval($pos->getZ(),$pos1->getZ(),$pos2->getZ())) 
			{
				if($nameOnly) return $name;
				else return $area;
			}
		}
		return null;
	}
	
	public function getConfig(Vector3 $pos)
	{
		$a = $this->getArea($pos);
		if($a === null) return $a;
		else return new Config($this->main->getDirectory()."property/".$a.".json", Config::JSON);
	}
	
	public function updatePlayerProperty(Player $p)
	{
		$pname = strtolower($p->getName());
		$p->property = array();
		
		foreach($this->getAreas() as $area)
		{
			$c = new Config($this->main->getDirectory()."property/".$area.".json", Config::JSON);
			
			foreach($c->getAll(true) as $name)
			{
				if($c->getNested("$name.owners") == $pname)
				{
					array_push($p->property, $name);
					
					$this->checkRented($name);
					
					if(count($p->property) > 1 and !$p->isOp()) return;
				}
			}
		}
	}
	
	public function checkRented($propertyName)
	{
		foreach($this->getAreas() as $area)
		{
			$c = new Config($this->main->getDirectory()."property/".$area.".json", Config::JSON);
			
			foreach($c->getAll(true) as $name)
			{
				$days = $c->getNested("$name.rented_days") - round((time() - $c->getNested("$name.rented")) / 86400);
				
				if($days < 1)
				{
					$c->setNested("$name.rented", 0);
					$c->setNested("$name.rented_days", 0);
					$c->setNested("$name.owners", false);
					$c->save();
				}
			}
		}
	}
}
?>

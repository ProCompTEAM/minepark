<?php  
namespace lifts; 

use lifts\Run; 
use pocketmine\Player; 
use pocketmine\block\Block;
use pocketmine\math\Vector3; 
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\level\Position; 
use pocketmine\command\Command; 
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase; 
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;

class Control extends PluginBase implements Listener 
{
	public function onEnable()
	{ 
		if(!file_exists($this->getDefaultDir())) mkdir($this->getDefaultDir()); 
		if(!file_exists($this->getDefaultDir()."speed.txt")) file_put_contents($this->getDefaultDir()."speed.txt", "2"); 
		$this->getServer()->broadcastMessage(TextFormat::GREEN."Теперь на Вашем сервере есть лифты!"); 
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->run = new Run($this); 
		$this->lifts = array(); 

		$result = file_get_contents($this->getDefaultDir()."speed.txt"); 
		if($result == "1") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 40); 
		elseif($result == "3") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 10); 
		elseif($result == "4") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 5); 
		else $this->getScheduler()->scheduleRepeatingTask(new Work($this), 20); 
	} 
	
	public function getDefaultDir() 
	{ 
		return "plugins/Lifts/"; 
	} 
	
	public function move(Position $pos) 
	{ 
		$x = $pos->getX();
		$y = $pos->getY()-1;
		$z = $pos->getZ();
		$w = $pos->getLevel(); 

		$allpos = array( 
			new Position($x, $y, $z, $w), 
			new Position($x+1, $y, $z, $w), 
			new Position($x, $y, $z+1, $w), 
			new Position($x-1, $y, $z, $w), 
			new Position($x, $y, $z-1, $w), 
			new Position($x+1, $y, $z+1, $w), 
			new Position($x-1, $y, $z-1, $w), 
			new Position($x-1, $y, $z+1, $w), 
			new Position($x+1, $y, $z-1, $w)); 

		foreach($allpos as $i) { 
			$w->setBlock(new Vector3($i->getX(), $i->getY(), $i->getZ()), Block::get(Block::QUARTZ_BLOCK)); 
		}
		$w->setBlock(new Vector3($x, $y, $z), Block::get(Block::IRON_BLOCK)); 
		
		foreach($allpos as $i) { $w->setBlock(new Vector3($i->getX(), $i->getY()+1, $i->getZ()), Block::get(Block::AIR)); } 
		foreach($allpos as $i) { $w->setBlock(new Vector3($i->getX(), $i->getY()-1, $i->getZ()), Block::get(Block::AIR)); } 
	} 
	
	public function clear(Position $pos) 
	{ 
		$x = $pos->getX();
		$y = $pos->getY()-1;
		$z = $pos->getZ();
		$w = $pos->getLevel(); 

		$allpos = array( 
			new Position($x, $y, $z, $w), 
			new Position($x+1, $y, $z, $w), 
			new Position($x, $y, $z+1, $w), 
			new Position($x-1, $y, $z, $w), 
			new Position($x, $y, $z-1, $w), 
			new Position($x+1, $y, $z+1, $w), 
			new Position($x-1, $y, $z-1, $w), 
			new Position($x-1, $y, $z+1, $w), 
			new Position($x+1, $y, $z-1, $w)
		);

		foreach($allpos as $i){
			$w->setBlock(new Vector3($i->getX(), $i->getY(), $i->getZ()), Block::get(Block::AIR));
		}
	} 
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $cmds) : bool
	{
		if($cmd == "lift") { 
			if(!isset($cmds[0])) return false;

			switch($cmds[0]) { 
				case "set": 
				if(!Empty($cmds[1])) { 
				$this->run->create($cmds[1], $sender->getPosition()); $sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* В базу добавлена/изменена точка лифта"); 
				} 
				else $this->showError($sender, 1); 
				break; 
				
				case "unset": 
				if(!Empty($cmds[1])) { 
				$this->run->remove($cmds[1]); 
				$sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* из базы удалена(если была в базе) точка лифта"); 
				} 
				else $this->showError($sender, 1); 
				break; 
				
				case "reload": 
				$this->run->loadAll(); 
				$sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* Список активных лифтов обновлен"); 
				break; 
				
				case "list": 
				$list = $this->run->scndr($this->getDefaultDir()."db/"); 
				$text = null; 
				foreach($list as $item) { 
					$text .= substr($item, 0, -4)." ; "; 
				} 
				$sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* Список лифтов: $text"); 
				break; 
				
				case "speed": 
				if($cmds[1] > 0 and $cmds[1] < 5) { 
					$sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* Скорость кабины изменена. По умолчанию она: 2"); 
					$sender->sendMessage(TextFormat::AQUA."*ЛИФТЫ* Эти параметры вступят в силу после перезапуска сервера!"); 
					$dir = $this->getDefaultDir()."speed.txt"; file_put_contents($dir, $cmds[1]); 
				} 
				else $this->showError($sender, 3);
				break; 
				
				case "help": 
				$menu = "§6ЛИФТЫ §1<- §aСоздатель плагина: §9vk.com/kirillporoh\n"; 
				$menu .= "§b↕ /lift set <id> - добавить точку лифта\n"; 
				$menu .= "§b↕ /lift unset <id> - удалить точку лифта\n"; 
				$menu .= "§b↕ /lift list - вывести список id's установленных лифтов\n"; 
				$menu .= "§b↕ /lift reload - перезапустить базовую конфигурацию\n"; 
				$menu .= "§b↕ /lift speed <1:2:3:4> - изменить скорость кабины (2 п.у)\n"; 
				$menu .= "§eЧтобы прокатиться на лифте, необходимо нажать на его центральный блок;\n"; 
				$menu .= "§6При установке лифта, нижний блок под ним - это этаж его остановки;\n"; 
				$menu .= "§cПосле создания точки - она превращается в работоспособную кабину;\n"; 
				$sender->sendMessage($menu); 
				break; 
			} 
		}

		return true;
	} 
	
	public function onClick(PlayerInteractEvent $e) 
	{ 
		$list = $this->run->getItems(); 

		$p = $e->getPlayer(); 
		$b = $e->getBlock(); 

		$x = floor($b->getX()); 
		$y = floor($b->getY()); 
		$z = floor($b->getZ());
		$wname = $p->getLevel()->getName(); 

		$form1 = "$x:$y:$z:$wname";

		if($e->getBlock()->getId() == 42 && is_array($list)) { 
			foreach($list as $i) { 
				$x = $i->getX(); $y = $i->getY()-1; $z = $i->getZ(); 
				$wname = $i->getLevel()->getName(); $form2 = "$x:$y:$z:$wname"; 
				if($form1 == $form2) { 
					$this->run->start($i, "down"); 
					$p->sendMessage(TextFormat::AQUA."*ЛИФТЫ*".TextFormat::GREEN." Лифт начал движение вниз!"); 
					return; 
				} 
			}
			
			$pos = new Position($b->getX(), $b->getY(), $b->getZ(), $p->getLevel()); 
			
			for ($a=0; $a < 124; $a++) { 
				$block = $pos->getLevel()->getBlock(new Vector3($pos->getX(), $pos->getY()+$a+1, $pos->getZ())); 
				if($block->getName() != "Air") break; 
			}

			$mpos = new Position($block->getX(), $block->getY()+1, $block->getZ(), $p->getLevel());

			foreach($list as $i) { 
				if($i->getX() == $mpos->getX() and $i->getY() == $mpos->getY() and $i->getZ() == $mpos->getZ() 
					and $i->getLevel()->getName() == $mpos->getLevel()->getName()) 
				{ 
					$this->run->start($mpos, "up"); $p->sendMessage(TextFormat::AQUA."*ЛИФТЫ*".TextFormat::GREEN." Лифт начал движение вверх!"); 
				} 
			}
		}
	} 
	
	public function showError(Player $p, $e_id = 0) 
	{ 
		$text = "произошел сбой в работе плагина"; 
		switch($e_id) { 
			case 1: $text = "неверный формат введенных данных команды"; break; 
			case 2: $text = "элемента с таким id нет в базе данных"; break; 
			case 3: $text = "допустимая скорость кабины: 1 / 2 / 3 / 4"; break; 
		} 
		$p->sendMessage(TextFormat::RED."*ЛИФТЫ*".TextFormat::GOLD." Ошибка: $text"); 
	} 
	
	public function getLocalPlayers(Position $pos) 
	{ 
		$plist = array(); 
		foreach($this->getServer()->getOnlinePlayers() as $p) {
			if($p->distance($pos) < 2) array_push($plist, $p); 
		} 
		return $plist; 
	} 
} 

class Work extends Task 
{ 
	public function __construct(Control $plugin)
	{
		$this->p = $plugin; 
		$this->p->work = $this; 
	} 
	
	public function onRun($currentTick) 
	{ 
		foreach($this->p->lifts as $key => $i) 
		{ 
			if($i[4] == 1) 
			{ 
				$y = $this->p->lifts[$key][1]->getY() + $this->p->lifts[$key][2]; 
				$this->p->clear($i[1]); 
				if($i[2] < 1) { 
					$this->p->move($this->p->lifts[$key][0]); 
					unset($this->p->lifts[$key]); 
					return; 
				} 
				$pos = new Position($i[0]->getX(), $y,$i[0]->getZ(), $i[0]->getLevel()); 
				$this->p->move($pos); 
				$this->p->lifts[$key][2]--;
			} else { 
				$y = $this->p->lifts[$key][1]->getY() + $this->p->lifts[$key][2] + 1; 

				if($this->p->lifts[$key][2] == 1) $this->p->clear($i[0]); 
				if($this->p->lifts[$key][2] == $i[4]) { 
					$posend = new Position($i[1]->getX(), $i[1]->getY()+1,$i[1]->getZ(), $i[1]->getLevel()); 
					$this->p->move($posend); 
					unset($this->p->lifts[$key]); 
					return; 
				}

				$pos = new Position($i[0]->getX(), $y,$i[0]->getZ(), $i[0]->getLevel()); 
				$this->p->move($pos);  
				$this->liftControl($pos); 
				$this->p->lifts[$key][2]++;  
			} 
		} 
	} 
	
	public function reload() 
	{ 
		$plugin = $this->p; 
		foreach($plugin->lifts as $key => $pos) { 
			if($plugin->lifts[$key][3] == false) { 
				$h = $pos[0]->getY() - $pos[1]->getY(); 
				$plugin->lifts[$key][2] = $h; 
				$plugin->lifts[$key][3] = true; 
				if($plugin->lifts[$key][4] == 2) { 
					$plugin->lifts[$key][4] = $h; 
					$plugin->lifts[$key][2] = 0; 
				} 
			} 
		} 
	} 
	
	public function liftControl(Position $pos) 
	{ 
		foreach($this->p->getLocalPlayers($pos) as $p) { 
			$p->teleport(new Vector3($p->x, $p->y + 1.1, $p->z));
		} 
		
	} 
}  
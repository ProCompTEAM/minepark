<?php
declare(strict_types = 1);

namespace Kirill_Poroh\games;

use Kirill_Poroh\MCRPG;

use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\utils\Color;
use pocketmine\block\Block;
use pocketmine\item\Item;

use pocketmine\event\Event;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use Kirill_Poroh\CallbackTask;

class Capture extends Game
{	
	public const MAX_PLAYERS_IN_TEAM = 1;
	public const ARENA_BLOCK_ID = 35; //wool
	
	public const TIMEOUT_WAIT = 60; //wait players
	public const TIMEOUT_CAPTURE = 120; //2 min : 1st
	public const TIMEOUT_BUILDING = 120; //2 min : 2st
	public const TIMEOUT_BATTLE = 180; //3 min : 3st
	public const TIMEOUT_SURVIVAL = 60; //1 min : final

	private $redteam;
	private $blueteam;
	
	private $redteam_blocks;
	private $blueteam_blocks;
	
	private $redteam_kills;
	private $blueteam_kills;
	
	private $timeout;
	
	private $matrix;

	public function __construct(MCRPG $plugin, string $levelName, string $name)
	{
		parent::__construct($plugin, $levelName, $name);
		
		$this->redteam = array();
		$this->blueteam = array();
		
		$this->redteam_kills = 0;
		$this->blueteam_kills = 0;
		
		$this->redteam_blocks = 0;
		$this->blueteam_blocks = 0;
		
		$this->timeout = 0;
		
		$this->matrix = array();
		
		//$plugin->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "timer"]), 20);
		//NEED TO TASK#125 IMPLEMENTATION
		
		$this->finished();
	}
	
	public function joinPlayer(Player $player)
	{	
		return; //NEED TO TASK#125 IMPLEMENTATION
	
		if($player === null or $player->getInventory() == null) return;
			
		$player->addTitle("§aПоехали!", "§7Добро пожаловать на §d" . $this->getGameName());
		
		$player->setXpLevel(0);
		
		$player->setGamemode($this->plugin->getServer()->getGamemodeFromString("0"));
		
		$player->getInventory()->clearAll();
		
		$player->setHealth($player->getMaxHealth());
		
		array_push($this->players, $player);
		
		$this->broadcastTitle("§eПополнение!", "§fС нами §b" . $player->getName(), $player);
		
		if(!$this->config->exists("red-x") or $this->status > 0)
		{
			$player->teleport($this->level->getSafeSpawn());
			
			$this->broadcast("§8Игрок " . $player->getName() . " переведен в наблюдатели!");
			$player->sendMessage("§7Игра уже началась, вы принимаете участие в качестве зрителя!");
		}
		
		elseif($this->status < 1)
		{
			if($this->getArenaOnline() < 1) $this->fillArena();
			
			if(count($this->redteam) <= count($this->blueteam))
			{
				array_push($this->redteam, $player);
				
				$online = count($this->redteam);
				
				$this->broadcast("§4Игрок " . $player->getName() . " играет за красных! §c($online/" . self::MAX_PLAYERS_IN_TEAM . ")");
				
				$player->setNameTag("§c" . $player->getNameTag());
				
				$pos = new Position(
					$this->config->get("red-x"),
					$this->config->get("red-y"),
					$this->config->get("red-z"),
					$this->level
				);
				
				$player->teleport($pos, 0, 0);
			}
			else
			{
				array_push($this->blueteam, $player);
				
				$online = count($this->blueteam);
				
				$this->broadcast("§1Игрок " . $player->getName() . " играет за синих! §c($online/" . self::MAX_PLAYERS_IN_TEAM . ")");
				
				$player->setNameTag("§9" . $player->getNameTag());
				
				$pos = new Position(
					$this->config->get("blue-x"),
					$this->config->get("blue-y"),
					$this->config->get("blue-z"),
					$this->level
				);
				
				$player->teleport($pos, 0, 0);
			}
			
			$this->updateArmor($player);
			
			if($this->getArenaOnline() == self::MAX_PLAYERS_IN_TEAM * 2) $this->waitPlayers();
			
			$player->sendMessage("§8Если Вы хотите выйти из игры: /exit");
		}
	}
	
	public function leavePlayer(Player $player)
	{
		return; //NEED TO TASK#125 IMPLEMENTATION
		
		$this->players = $this->removePlayer($this->players, $player);
		$this->redteam = $this->removePlayer($this->redteam, $player);
		$this->blueteam = $this->removePlayer($this->blueteam, $player);
		
		if($this->isOnline($player))
		{
			$player->setXpLevel(0);
			
			$player->getInventory()->clearAll();
			
			$player->setNameTag($player->getName());
			
			$player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
			
			$player->sendMessage("§7Вы отключены от арены §3" . $this->name);
		}
		
		$this->broadcastTitle("§c-§a". $player->getName(), "§7Press F to pay respect.");
	}
	
	public function handle(Event $e)
	{
		if($e instanceof EntityDamageByEntityEvent and $e->getDamager() instanceof Player 
			and $e->getEntity() instanceof Player and $e->getEntity()->getLevel() == $this->level)
		{
			if($this->status > 2 and 
				($this->existsElement($this->redteam, $e->getEntity()) or $this->existsElement($this->blueteam, $e->getEntity())))
			{
				if($e->getEntity()->getHealth() - $e->getFinalDamage() <= 0) //death
				{
					if($this->existsElement($this->redteam, $e->getEntity()))
					{
						$pos = new Position(
						$this->config->get("red-x"),
						$this->config->get("red-y"),
						$this->config->get("red-z"),
						$this->level
						);
						$e->getEntity()->teleport($pos);
							
						$this->blueteam_kills++;
							
						$this->broadcast($e->getDamager()->getNameTag() . " §6смертельно ранил " . $e->getEntity()->getNameTag());
						
						$e->getEntity()->setHealth($e->getEntity()->getMaxHealth());
						
						if($this->status == 4) $this->leavePlayer($e->getEntity());
						
						$this->checkArena();
					}
					else
					{
						$pos = new Position(
						$this->config->get("blue-x"),
						$this->config->get("blue-y"),
						$this->config->get("blue-z"),
						$this->level
						);
						$e->getEntity()->teleport($pos);
						
						$this->redteam_kills++;
						
						$this->broadcast($e->getDamager()->getNameTag() . " §6смертельно ранил " . $e->getEntity()->getNameTag());
					
						$e->getEntity()->setHealth($e->getEntity()->getMaxHealth());
						
						if($this->status == 4) $this->leavePlayer($e->getEntity());
					
						$this->checkArena();
					}
				}
				else $this->broadcastPopup("§6!!! §e<=- §cкоманду §9атакуют §e-=> §6!!!");
			}
			else $e->setCancelled(true);
		}
		elseif(!($e instanceof EntityDamageEvent) and $this->level == $e->getPlayer()->getLevel())
		{
			//capture step
			if($e instanceof PlayerInteractEvent and $this->status == 1)
			{
				if($e->getBlock()->getId() == self::ARENA_BLOCK_ID)
				{
					if($this->existsElement($this->redteam, $e->getPlayer()) 
						and !isset($this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())]))
					{
						$this->level->setBlock($e->getBlock(), Block::get(self::ARENA_BLOCK_ID, 6));
						$this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())] = 1;
						$this->redteam_blocks++;
					}
					elseif($this->existsElement($this->blueteam, $e->getPlayer()) 
						and !isset($this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())]))
					{
						$this->level->setBlock($e->getBlock(), Block::get(self::ARENA_BLOCK_ID, 3)); 
						$this->matrix[floor($e->getBlock()->getX())][floor( $e->getBlock()->getZ())] = 2;
						$this->blueteam_blocks++;
					}
				}
			}
			
			//building step
			elseif(($e instanceof BlockBreakEvent or $e instanceof BlockPlaceEvent) and $this->status < 3)
			{	
				if($this->existsElement($this->redteam, $e->getPlayer()) and $this->status == 2)
				{
					if(isset($this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())]) and 
						$this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())] == 1
						and $e->getBlock()->getY() + 1 > min($this->config->get("y1"), $this->config->get("y2"))
								and $e->getBlock()->getY() < max($this->config->get("y1"), $this->config->get("y2"))) $e->setCancelled(false);
					else
					{
						$e->getPlayer()->sendPopup("§6!!! Это не ваша территория !!!");
						$e->setCancelled(true);
					}
				}
				elseif($this->existsElement($this->blueteam, $e->getPlayer()) and $this->status == 2)
				{
					if(isset($this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())]) and 
						$this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())] == 2
							and $e->getBlock()->getY() + 1 > min($this->config->get("y1"), $this->config->get("y2"))
								and $e->getBlock()->getY() < max($this->config->get("y1"), $this->config->get("y2"))) 
									$e->setCancelled(false);
					else
					{
						$e->getPlayer()->sendPopup("§6!!! Это не ваша территория !!!");
						$e->setCancelled(true);
					}
				}
				else $e->setCancelled(true);
			}
			
			//battle step
			elseif(($e instanceof BlockBreakEvent or $e instanceof BlockPlaceEvent) and $this->status >= 3)
			{
				if(isset($this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())]) and 
						$this->matrix[floor($e->getBlock()->getX())][floor($e->getBlock()->getZ())] > 0) $e->setCancelled(false);
				else $e->setCancelled(true);
			}
			
			//fix of death and etc
			elseif($e instanceof EntityTeleportEvent and $e->getEntity() instanceof Player)
			{
				if($e->getTo()->getLevel() !== $this->level) $this->leavePlayer($e->getEntity());
			}
		}
	}
	
	public function command(Player $player, array $params)
	{
		return; //NEED TO TASK#125 IMPLEMENTATION
		
		if($params[0] == "info")
			$player->sendMessage("§dЗахвати территорию, построй оборону, защищайся!");
		
		elseif($params[0] == "setred" and $player->isOp())
		{	
			$this->config->set("red-x", floor($player->getX()));
			$this->config->set("red-y", floor($player->getY()));
			$this->config->set("red-z", floor($player->getZ()));
			
			$this->config->save();
			
			$player->sendMessage("§4[*] Точка появления красных установлена!");
		}
		
		elseif($params[0] == "setblue" and $player->isOp())
		{
			$this->config->set("blue-x", floor($player->getX()));
			$this->config->set("blue-y", floor($player->getY()));
			$this->config->set("blue-z", floor($player->getZ()));
			
			$this->config->save();
			
			$player->sendMessage("§1[*] Точка появления синих установлена!");
		}
		
		elseif($params[0] == "set1" and $player->isOp())
		{
			$this->config->set("x1", floor($player->getX()));
			$this->config->set("y1", floor($player->getY()));
			$this->config->set("z1", floor($player->getZ()));
			
			$this->config->save();
			
			$player->sendMessage("§e#1 позиция арены установлена");
		}
		
		elseif($params[0] == "set2" and $player->isOp())
		{
			$this->config->set("x2", floor($player->getX()));
			$this->config->set("y2", floor($player->getY()));
			$this->config->set("z2", floor($player->getZ()));
			
			$this->config->save();
			
			$player->sendMessage("§e#2 позиция арены установлена");
		}
	}
	
	public function timer()
	{
		if($this->status != 0) $this->timeout--;
			
		foreach($this->players as $p)
		{
			$p->setXpLevel($this->timeout);
				
			if($this->timeout < 5 and $this->status != 0) $this->broadcastTitle("§e" . $this->timeout, "");
		}
			
		if($this->timeout == 0)
			{	
			if($this->status > 0) $this->checkArena();
				
			switch($this->status)
			{
				case -1: $this->startGame(); break;
				case 1: $this->stepBuilding(); break;
				case 2: $this->stepTeamBattle(); break;
				case 3: $this->stepSurvival(); break;
				case 4: $this->stopGame(); break;
			}
				
			foreach($this->redteam as $p) $this->updateArmor($p);
			foreach($this->blueteam as $p) $this->updateArmor($p);
		}
	}
	
	public function waitPlayers()
	{
		$this->status = -1;
		
		$this->timeout = self::TIMEOUT_WAIT;
		
		$this->broadcast("§aОжидание игроков...");
	}
	
	public function startGame()
	{
		$this->matrix = array();
		
		$this->status = 1;
		
		$this->timeout = self::TIMEOUT_CAPTURE;
		
		$this->broadcastAll("Игра на арене §e" . $this->name . " §fначалась!");
		
		$this->broadcastTitle("§aИгра началась!", "§3Первый этап: §fбитва за территорию!");
		
		$this->broadcast("§a<============================>");
		$this->broadcast("§2Нажимайте на блоки под ногами, увеличивая размер зоны команды!");
		$this->broadcast("§eСделайте это быстрее соперника! Как можно быстрее!!!");
		$this->broadcast("§6На следущем этапе вы будете всей командой строить на ней!");
		$this->broadcast("§a<============================>");
	}
	
	public function stepBuilding()
	{	
		$this->status = 2;
		
		$this->timeout = self::TIMEOUT_BUILDING;
		
		$this->broadcastTitle("§eПродолжаем!", "§3Второй этап: §fстроительство!");
		
		$this->broadcast("§c> Красные закрасили §f - §e" . $this->redteam_blocks);
		$this->broadcast("§9> Синие закрасили §f - §e" . $this->blueteam_blocks);
		
		$this->broadcast("§a<============================>");
		$this->broadcast("§2Блоки цвета вашей команды под ногами - ваша территория!");
		$this->broadcast("§eСтройте оборону, ограничения и укрепления из блоков поверх их.");
		$this->broadcast("§6Учтите, что всем выдано равное, но небольшое кол-во блоков.");
		$this->broadcast("§a<============================>");
		
		//inventory
		$iR = Item::get(159, 6, 128);
		$iB = Item::get(159, 3, 128);
		$i1 = Item::get(285, 0, 1);
		$i2 = Item::get(260, 0, 16);
		$i3 = Item::get(30, 0, 1);
		$i4 = Item::get(54, 0, 1);
		
		foreach($this->players as $p) 
		{
			if($this->existsElement($this->redteam, $p) or $this->existsElement($this->blueteam, $p))
			{
				if($this->existsElement($this->redteam, $p))
					$p->getInventory()->addItem($iR);
				else $p->getInventory()->addItem($iB);
				
				$p->getInventory()->addItem($i1);
				$p->getInventory()->addItem($i2);
				$p->getInventory()->addItem($i3);
				$p->getInventory()->addItem($i4);
			}
		}
		
		$this->broadcastPopup("§e+!! В Ы Д А Н Ы   Р Е С У Р С Ы !!+");
	}
	
	public function stepTeamBattle()
	{
		$this->status = 3;
		
		$this->timeout = self::TIMEOUT_BATTLE;
		
		$this->broadcastTitle("§6В бой!!!", "§3Третий этап: §fсражение за команду!");
		
		$this->broadcast("§a<============================>");
		$this->broadcast("§2Сейчас вы должны нанести урон команде соперника");
		$this->broadcast("§eКомандая набравшая больше киллов побеждает!");
		$this->broadcast("§6Если вы выиграете, получите денежный приз!");
		$this->broadcast("§a<============================>");
		
		//inventory
		$i1 = Item::get(276, 0, 1);
		$i2 = Item::get(322, 0, 3);
		$i3 = Item::get(332, 0, 8);
		$i4 = Item::get(261, 0, 1);
		$i5 = Item::get(262, 0, 32);
		
		foreach($this->players as $p) 
		{
			if($this->existsElement($this->redteam, $p) or $this->existsElement($this->blueteam, $p))
			{
				$p->getInventory()->addItem($i1);
				$p->getInventory()->addItem($i2);
				$p->getInventory()->addItem($i3);
				$p->getInventory()->addItem($i4);
				$p->getInventory()->addItem($i5);
			}
		}
		
		$this->broadcastPopup("§4+!! В Ы Д А Н Ы   Р Е С У Р С Ы !!+");
	}
	
	public function stepSurvival()
	{
		$this->teamWinRequest();
		
		$this->status = 4;
		
		$this->timeout = self::TIMEOUT_SURVIVAL;
		
		$this->broadcastTitle("§4Ф И Н А Л !!!", "§0Заключение: §eбитва за жизнь!");
		
		$this->broadcast("§a<============================>");
		$this->broadcast("§2У вас только одна жизнь! Будьте осторожны!");
		$this->broadcast("§eВаша задача убить всех! Прямо сейчас!");
		$this->broadcast("§6Победитель получает джекпот!");
		$this->broadcast("§a<============================>");
	}
	
	public function stopGame()
	{
		$this->status = 0;
		$this->timeout = 0;
		
		$this->playerWinRequest();
		
		foreach($this->players as $p) $this->leavePlayer($p);
		
		$this->broadcastAll("На арене §e" . $this->name . " §f завершена игра. Ждем игроков снова: §6/game " . $this->name);
	}
	
	public function fillArena()
	{
		if($this->config->exists("x1") and $this->config->exists("z2"))
		{
			$minX = min($this->config->get("x1"), $this->config->get("x2"));
			$minY = min($this->config->get("y1"), $this->config->get("y2"));
			$minZ = min($this->config->get("z1"), $this->config->get("z2"));
			
			$maxX = max($this->config->get("x1"), $this->config->get("x2")) + 1;
			$maxY = max($this->config->get("y1"), $this->config->get("y2"));
			$maxZ = max($this->config->get("z1"), $this->config->get("z2")) + 1;
			
			for($x = $minX; $x < $maxX; $x++)
				for($y = $minY; $y < $maxY; $y++)
					for($z = $minZ; $z < $maxZ; $z++)
						if($this->level->getBlockIdAt((int) $x, (int) $y, (int) $z) != 0)
							$this->level->setBlockIdAt((int) $x, (int) $y, (int) $z, 0);
				
			for($x = $minX; $x < $maxX; $x++)
				for($z = $minZ; $z < $maxZ; $z++)
					$this->level->setBlock(new Vector3($x, $minY + 1, $z), Block::get(self::ARENA_BLOCK_ID, 0));
				
			$this->broadcast("§8Арена для игры полностью очищена и готова к игре!");
		}
	}
	
	public function checkArena()
	{
		foreach($this->players as $p)
		{
			if($p->getLevel() !== $this->level
				or !$this->isOnline($p)) $this->leavePlayer($p); 
			else
			{
				$minX = min($this->config->get("x1"), $this->config->get("x2"));
				$minZ = min($this->config->get("z1"), $this->config->get("z2"));
			
				$maxX = max($this->config->get("x1"), $this->config->get("x2")) + 1;
				$maxZ = max($this->config->get("z1"), $this->config->get("z2")) + 1;
			
				if(($p->getX() < $minX or $p->getX() > $maxX or
					$p->getZ() < $minZ or $p->getZ() > $maxZ) and
						($this->existsElement($this->redteam, $p) or $this->existsElement($this->blueteam, $p)))
				{
					$this->broadcast("§4Игроку " . $p->getName() . " не стоило выходить за границы!!!");
					$this->leavePlayer($p); 
				}
			}
		}
		
		if(count($this->redteam) < 1 or count($this->blueteam) < 1) $this->stopGame();
	}
	
	public function teamWinRequest()
	{
		if($this->redteam_kills > $this->blueteam_kills)
		{
			$this->broadcastTitle("§cКрасные выиграли!", "§4Команда набрала " . $this->redteam_kills . " очков");
		}
		elseif($this->redteam_kills < $this->blueteam_kills)
		{
			$this->broadcastTitle("§9Синие выиграли!", "§1Команда набрала " . $this->blueteam_kills . " очков");
		}
		else $this->broadcastTitle("§aНичья!", "§0Обе команды оказались сильны!");
		
		$this->broadcastAll("Финал на арене §e" . $this->name . "§f, счет: §d" . $this->redteam_kills . ":" . $this->blueteam_kills);
	}
	
	public function playerWinRequest()
	{
		if($this->getArenaOnline() == 1)
		{
			$winner = null;
			
			if(count($this->redteam) > 0)
			{
				$winner = $this->redteam[0];
				$this->broadcastTitle("§c" . $winner->getName(), "§aОдержал победу! Поздравляем!");
			}
			else 
			{
				$winner = $this->blueteam[0];
				$this->broadcastTitle("§9" . $winner->getName(), "§aОдержал победу! Поздравляем!");
			}
			
			$this->broadcastAll("На арене §e" . $this->getName() . " §f победу одержал §3" . $winner->getName() . "§f. Поздравляем его!");
		}
		else $this->broadcastTitle("§eРавная игра!", "§fНе удалось выявить победителя!");
	}
	
	public function updateArmor(Player $player)
	{
		$helmet = Item::get(302, 0, 1);
		$cp = Item::get(299, 0, 1);
		$leg = Item::get(304, 0, 1);
		$boots = Item::get(305, 0, 1);
		
		if($this->existsElement($this->redteam, $player))
			$cp->setCustomColor(new Color(179,49,44));
		else $cp->setCustomColor(new Color(37,49,146));
		
		$player->getArmorInventory()->setHelmet($helmet);
		$player->getArmorInventory()->setChestplate($cp);
		$player->getArmorInventory()->setLeggings($leg);
		$player->getArmorInventory()->setBoots($boots);
	}
	
	public function getGameName() : string
	{
		return "Захват Территорий";
	}
	
	public function getArenaOnline() : int
	{
		return count($this->redteam) + count($this->blueteam);
	}
}

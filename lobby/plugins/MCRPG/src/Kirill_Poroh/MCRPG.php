<?php
declare(strict_types = 1);

namespace Kirill_Poroh;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Painting;

use pocketmine\block\Block;
use pocketmine\item\Item;

use pocketmine\level\particle\FloatingTextParticle;

use pocketmine\entity\Human;

use Kirill_Poroh\StructMap;
use Kirill_Poroh\CallbackTask;
use Kirill_Poroh\Marquee;


class MCRPG extends PluginBase implements Listener 
{	
	public $players_params;
	
	public $games;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->load();
		
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "save"]), 20 * 60 * 5);
		
		//game rooms
		$this->games = array();
		
		array_push($this->games, new games\Capture($this, "sportcapt", "capt"));
	}
	
	public function onDisable()
	{
		$this->save();
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
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
						
						$player->close("", "§aВам установлена группа §3" . $args[1]);
						$sender->sendMessage("§6Вы установили группу игроку на §e" . $args[1]);
					}
					else $this->players_params[strtolower($args[0])]["group"] = $args[1];
				}
			break;
			
			case "game":
				return; //NEED TO TASK#125 IMPLEMENTATION
				if(isset($args[0]) and $sender instanceof Player and !$this->isInGame($sender))
				{
					$g = $this->getGame($args[0]);
					
					if($g !== null)
					{
						if(!isset($args[1])) $g->joinPlayer($sender);
						else $g->command($sender, array_splice($args, 1));
					}
					else $sender->sendMessage("§cИгры с таким именем  не существует :(");
				}
				else $sender->sendMessage("§cФормат: /game <имя> <?параметры>");
			break;
			
			case "exit":
				if($this->isInGame($sender))
					foreach($this->games as $g) $g->leavePlayer($sender);
				else $sender->sendMessage("§cВы вне игры!");
			break;
		}
		
		return true;
	}
	
	public function playerTouchOnNPCEvent(EntityDamageEvent $e)
	{	
		if(($e instanceof EntityDamageByEntityEvent 
			and $e->getEntity() instanceof Painting) and ($e->getDamager() instanceof Player and !$e->getDamager()->isOp())) $e->setCancelled(true);
		
		foreach($this->games as $g) $g->handle($e);
	}
	
	public function playerJoinEvent(PlayerJoinEvent $e)
	{
		$id = $this->getPlayerID($e->getPlayer());
		
		if(!isset($this->players_params[$id]))
		{
			$this->players_params[$id]["coins"] = 0;
			$this->players_params[$id]["group"] = null;
			$this->players_params[$id]["registered"] = time();
		}
		
		$this->players_params[$id]["logintime"] = time();
		
		$e->getPlayer()->sm = new StructMap($this, $e->getPlayer());
		
		$this->checkGroup($e->getPlayer());
		
		//GIVING ITEMS DEFAULT KIT
		$item1 = Item::get(266, 0, 1);
		$item1->setCustomName("§cROLE §aPLAY §eSERVER §8- §61.14.X+");
		$item2 = Item::get(265, 0, 1);
		$item2->setCustomName("§2SURVIVAL SERVER §8- §91.X.X+");
		$item3 = Item::get(336, 0, 1);
		$item3->setCustomName("§0<<< §3ЗаХвАт ТеРРиТоРиЙ §0>>>");
		
		if(!$e->getPlayer()->getInventory()->contains($item1)) 
			$e->getPlayer()->getInventory()->setItem(0,$item1);
		
		if(!$e->getPlayer()->getInventory()->contains($item2)) 
			$e->getPlayer()->getInventory()->setItem(1,$item2);
		
		if(!$e->getPlayer()->getInventory()->contains($item3)) 
			$e->getPlayer()->getInventory()->setItem(2,$item3);
	}
	
	public function playerQuitEvent(PlayerQuitEvent $e)
	{
		foreach($this->games as $g) $g->leavePlayer($e->getPlayer());
	}
	
	public function playerPlaceBlock(BlockPlaceEvent $e)
	{
		foreach($this->games as $g) $g->handle($e);
	}
	
	public function playerBreakBlock(BlockBreakEvent $e)
	{	
		foreach($this->games as $g) $g->handle($e);
	}
	
	public function playerTapEvent(PlayerInteractEvent $e)
	{
		//CHECK ITEMS DEFAULT KIT
		if($e->getItem()->getId() == 266) $this->sendCommand($e->getPlayer(), "rp"); 
		if($e->getItem()->getId() == 265) $this->sendCommand($e->getPlayer(), "survival"); 
		if($e->getItem()->getId() == 336) $this->sendCommand($e->getPlayer(), "game capt"); 
		
		//OTHER
		foreach($this->games as $g) $g->handle($e);
	}
	
	public function sendCommand(Player $player, string $command)
	{
		$this->getServer()->dispatchCommand($player, $command);
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
		}
		
		if($player->hasPermission("group.custom"))
		{
			foreach($this->getServer()->getOnlinePlayers() as $p)
				$p->sendTip($player->subtag . $player->getName() . " §dвыбирает сервер для игры..");
		}
	}
	
	
	//   F U N C T I O N S
	
	public function getPlayerID(Player $player)
	{
		return strtolower($player->getName());
	}
	
	public function getDirectory()
	{
		@mkdir("mcrpg_resources/");
		return "mcrpg_resources/";
	}
	
	public function getPrefix()
	{
		return "§6MINE§aPARK§8.§eRU §7▶ ";
	}
	
	public function save()
	{
		$json = json_encode($this->players_params,  JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
		file_put_contents($this->getDirectory() . "players.auto.json", $json);
	}
	
	public function load()
	{
		if(file_exists($this->getDirectory() . "players.auto.json"))
			$this->players_params = json_decode(file_get_contents($this->getDirectory() . "players.auto.json"), true);
		else $this->players_params = array();
	}
	
	public function getGame(string $name)
	{
		foreach($this->games as $g)
		{
			if($g->getName() == $name) return $g;
		}
	}
	
	public function isInGame(Player $player) : bool
	{
		foreach($this->games as $g)
		{
			foreach($g->getPlayers() as $p)
				if($p === $player) return true;
		}
		
		return false;
	}
	
	public function setPermission(Player $player, $perm, $status = true)
	{
		$player->addAttachment($this, $perm, $status);
	}
}

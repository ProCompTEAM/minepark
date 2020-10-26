<?php
/* 
Все права на данный плагин пренадлежат его автору!
Дата начала создания плагина: 05.12.2018
Я ВКОНТАКТЕ: https://vk.com/tnull2
*/
namespace SmartTransport;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\entity\Entity;
use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use SmartTransport\Model;
use SmartTransport\TaxiModel;
use SmartTransport\Car1Model;
use SmartTransport\Car2Model;
use SmartTransport\Car3Model;
use SmartTransport\Car4Model;
use SmartTransport\Train;

class Transport extends PluginBase implements Listener 
{
	private $models;
	private $ent;
	
	public function onEnable()
	{
		$this->getLogger()->info("Плагин, добавляющий транспорт, запущен!");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->models = array(
			new TaxiModel(),
			new Car1Model(),
			new Car2Model(),
			new Car3Model(),
			new Car4Model(),
			new Train()
		);
		
		$this->ent = array();
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
	{
		if($command->getName() == "t" and isset($args[0]))
		{
			if($args[0] == "models")
			{
				$sender->sendMessage("§aДоступные модели транспорта: ");
				
				foreach($this->models as $model)
					$sender->sendMessage("§e - §a" . $model->getName());
			}
			elseif($args[0] == "spawn" and isset($args[1]) and $sender->isOp())
			{
				$model = $this->getModel($args[1]);
				
				if($model !== null)
				{
					$this->spawnTo($model, $sender->getPosition(), $sender->getYaw());
					
					$sender->sendMessage("§eТранспорт модели §e" . $model->getName() . " §eсоздан здесь!");
				}
				else $sender->sendMessage("§cТранспорта модели §e" . $args[1] . " §cнет в базе!");
			}
			elseif($args[0] == "drivetrain" and $sender->transport == null)
			{
				$model = new Train();
				
				$entity = $this->spawnTo($model, $sender->getPosition(), $sender->getYaw());
				
				$this->ent[$entity->getId()] = $model;
				
				$sender->transport = $entity;
				
				$this->ent[$entity->getId()]->setDriver($sender);
				
				$entity->setNameTag("§a/t join §3- §eБЕСПЛАТНАЯ ПОЕЗДКА");
				
				$this->getServer()->broadcastMessage("§9По установленному маршруту выехал поезд! Поезда на рельсах: /t trains");
				
				$sender->sendMessage("§cПожалуйста, проведите путь полностью, а только потом закончите маршрут!!!");
				
				$sender->sendMessage("§eМаршрут начинается в депо и здесь же закончится. Движение против часовой стрелки!");
				
				$sender->sendMessage("§6При движении поезда вне рельс игрок немедленно блокируется адмистрацией!");
				
				$sender->sendMessage("§dОстанавливайтесь на станциях, за каждого пассажира вы получаете деньги!");
				
				$sender->sendMessage("§7Закончить маршрут: /t endtrain");
				
				$this->hideInTransport($sender);
			}
			elseif($args[0] == "endtrain" and $sender->transport != null)
			{
				$this->leaveFromTransport($sender);
				$this->showInTransport($sender);
				
				$sender->sendMessage("§7Вы закончили маршрут! Снова: /t drivetrain");
			}
			elseif($args[0] == "trains")
			{
				$trains = 0;
				
				foreach($this->getServer()->getOnlinePlayers() as $p)
					if(isset($p->transport) and $p->transport != null and $p->transport->transport->trainIs()) $trains++;
					
				$sender->sendMessage("Сейчас на ЖД путях найдено вагонов: §6" . $trains);
			}
			elseif($args[0] == "join" and $sender->transport == null)
			{
				foreach($sender->getLevel()->getEntities() as $entity) 
				{
					if($entity->distance($sender) <= 6 and isset($this->ent[$entity->getId()]) and $this->ent[$entity->getId()] != null) 
					{
						$this->ent[$entity->getId()]->transport->addPerson($sender);
						
						$this->hideInTransport($sender);
						
						$sender->setImmobile(true);
						
						$sender->sendMessage("§dВы сели пассажиром.");
						$sender->sendMessage("§4Выйти: /t stop");
						
						if($this->ent[$entity->getId()]->transport->getDriver() != null)
							$this->ent[$entity->getId()]->transport->getDriver()->sendMessage("§1В ваш ТС сел пассажир.");
						
						return true;
					}
				} 
			}
			elseif($args[0] == "stop" and $sender->transport == null)
			{
				foreach($sender->getLevel()->getEntities() as $entity) 
				{
					if($this->ent[$entity->getId()]->distance($sender) <= 3 and isset($entity->transport) and $entity->transport != null) 
					{
						$this->ent[$entity->getId()]->transport->removePerson($sender);
						
						$sender->setImmobile(false);
						
						$this->showInTransport($sender);
						
						$sender->sendMessage("§aВы вышли из транспортного средства.");
						
						if($this->ent[$entity->getId()]->transport->getDriver() != null)
							$this->ent[$entity->getId()]->transport->getDriver()->sendMessage("§9Из вашего ТС вышел пассажир.");
						
						return true;
					}
				} 
			}
			else $sender->sendMessage("§cНекорректно! Команды: /t models, /t spawn <модель>, /t trains, /t join");
		}
		return true;
	}
	
	public function getModel(string $modelName) : ?Model
	{
		foreach($this->models as $model) 
			if(strtolower($model->getName()) == strtolower($modelName)) return $model;
		
		return null;
	}
	
	public function spawnTo(Model $model, Position $pos, int $yaw = 0) : Entity
	{
		return $model->createEntity($pos, $yaw);
	}
	
	public function playerDmgEvent(EntityDamageEvent $e)
	{
		if($e instanceof EntityDamageByEntityEvent and $e->getDamager() instanceof Player and !$e->getEntity() instanceof Player)
		{
			if($e->getEntity() instanceof \pocketmine\entity\Villager)
			{
				if(!isset($this->ent[$e->getEntity()->getId()]) or $this->ent[$e->getEntity()->getId()] == null)
				{
					if($e->getEntity() instanceof \pocketmine\entity\Villager)
					{
						switch($e->getEntity()->getProfession())
						{
							case 0: $this->ent[$e->getEntity()->getId()] = new TaxiModel();
							case 1: $this->ent[$e->getEntity()->getId()] = new Car1Model();
							case 2: $this->ent[$e->getEntity()->getId()] = new Car2Model();
							case 3: $this->ent[$e->getEntity()->getId()] = new Car3Model();
							case 4: $this->ent[$e->getEntity()->getId()] = new Car4Model();
						}
					}
					elseif($e->getEntity() instanceof mob\Minecart)
					{
						$this->ent[$e->getEntity()->getId()] = new Train();
					}
				}
				
				if(isset($this->ent[$e->getEntity()->getId()]) and $this->ent[$e->getEntity()->getId()]->getDriver() == null)
				{
					$this->ent[$e->getEntity()->getId()]->setDriver($e->getDamager());
						
					$e->getDamager()->transport = $e->getEntity();
					
					$e->getEntity()->setRotation($e->getDamager()->getYaw(), 90); //TODO 1.12
					
					$e->getDamager()->sendWindowMessage(file_get_contents("pdd.txt")); /////////////////////////////////////////////////////////////////////////////////////
						
					$e->getDamager()->sendMessage("§eВы управляете транспортным средством!");
					$e->getDamager()->sendMessage("§5Для пассажира(рядом): /t join");
					$e->getDamager()->sendMessage("§8Чтобы выйти - тапните внутри машины.");
					
					$this->hideInTransport($e->getDamager());
				}
				elseif(isset($this->ent[$e->getEntity()->getId()]) and $this->ent[$e->getEntity()->getId()]->getDriver() != null 
					and $this->ent[$e->getEntity()->getId()]->getDriver()->getName() == $e->getDamager()->getName())
				{
					$this->leaveFromTransport($e->getDamager());
						
					if($e->getDamager()->transport instanceof mob\Minecart)
						$e->getDamager()->sendMessage("§8Вы закончили вождение поезда!");
					else $e->getDamager()->sendMessage("§8Вы покинули ТС...");
						
					$this->showInTransport($e->getDamager());
				}
			}
				
			$e->setCancelled();
		}
	}
	
	public function playerJoinEvent(PlayerJoinEvent $e)
	{
		$e->getPlayer()->transport = null;
		
		$e->getPlayer()->sborder = $e->getPlayer()->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->getValue();
	}
	
	public function playerQuitEvent(PlayerQuitEvent $e)
	{
		if(isset($e->getPlayer()->transport) and $e->getPlayer()->transport != null) $this->leaveFromTransport($e->getPlayer());
	}
	
	public function playerMoveEvent(PlayerMoveEvent $e)
	{
		if($e->getPlayer()->transport != null)
		{
			$e->getPlayer()->transport->setPosition($e->getPlayer()->getPosition());
			
			if($this->ent[$e->getPlayer()->transport->getId()]->getPosition() != $e->getPlayer()->getPosition())
			{
				$this->ent[$e->getPlayer()->transport->getId()]->setPosition($e->getPlayer()->getPosition()); 
				
				$e->getPlayer()->transport->setRotation($e->getPlayer()->getYaw(), -90);
				
				$this->ent[$e->getPlayer()->transport->getId()]->addSpeed();
				
				foreach($this->ent[$e->getPlayer()->transport->getId()]->getPassengers() as $p) 
				{
					$p->teleport($e->getPlayer()->getPosition());
					$p->setRotation($e->getPlayer()->getYaw(), $e->getPlayer()->getPitch());
				}
			}
			else $this->ent[$e->getPlayer()->transport->getId()]->clearSpeed();
			
			$e->getPlayer()->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue((1 + $this->ent[$e->getPlayer()->transport->getId()]->getSpeed()) / 170); 
				
			$e->getPlayer()->sendPopup("§aкм/ч §f" . $this->ent[$e->getPlayer()->transport->getId()]->getSpeed());
		}
	}
	
	public function hideInTransport(Player $player)
	{
		foreach($this->getServer()->getOnlinePlayers() as $p) $p->hidePlayer($player);			
	}
	
	public function showInTransport(Player $player)
	{
		foreach($this->getServer()->getOnlinePlayers() as $p) $p->showPlayer($player);			
	}
	
	public function leaveFromTransport(Player $player)
	{
		foreach($this->ent[$player->transport->getId()]->getPassengers() as $p)
		{
			$p->setImmobile(false);
						
			$this->showInTransport($p);
						
			$p->sendMessage("§4Водитель покинул ТС. Вы покинули ТС!");
		}
		
		$this->ent[$player->transport->getId()]->setDriver(null);
				
		if($player->transport instanceof mob\Minecart) 
				$player->transport->kill(); 
			
		$player->transport = null;
		
		$player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($player->sborder); 
	}
}
?>
<?php
namespace minepark;

use pocketmine\tile\Sign;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use pocketmine\event\Listener;
use minepark\utils\FixSignEvent;
use minepark\components\GameChat;
use pocketmine\entity\object\Painting;
use pocketmine\event\block\BlockEvent;
use minepark\providers\data\UsersSource;
use minepark\common\player\MineParkPlayer;
use pocketmine\event\block\BlockBurnEvent;
use pocketmine\event\level\ChunkLoadEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\item\Item;

class EventsHandler implements Listener
{
    public const ENABLE_SIGN_EMULATION = false;

	public function getCore() : Core
	{
		return Core::getActive();
	}
    
    public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(MineParkPlayer::class);
	}
	
	public function commandEvent(PlayerCommandPreprocessEvent $event)
	{
		$player = MineParkPlayer::cast($event->getPlayer());
		$playerName = $player->getName();
		$message = $event->getMessage();

		if(!$player->getStatesMap()->auth) {
			$this->getCore()->getAuthModule()->login($player, $message);
			$event->setCancelled();
			return;
		}

		$this->getCore()->getApi()->sendToMessagesLog($playerName, $message);
		$this->getCore()->getCommandsHandler()->execute($player, $message, $event);
		$this->getCore()->getOrganisationsModule()->getCommandHandler()->execute($player, $message, $event);
	}
	
	public function joinEvent(PlayerJoinEvent $event)
	{
		$this->getCore()->getApi()->sendToMessagesLog($event->getPlayer()->getName(), "Вход осуществлен ***");

		$this->getCore()->getAuthModule()->preLogin($event->getPlayer());
		
		$this->getCore()->getInitializer()->join($event->getPlayer());

		$this->getUsersSource()->updateUserJoinStatus($event->getPlayer()->getName());

		$event->setJoinMessage(null);
	}
	
	public function chatEvent(PlayerChatEvent $event)
	{
		$event->setCancelled();

		$player = MineParkPlayer::cast($event->getPlayer());
		$message = $event->getMessage();

		if ($player->getStatesMap()->phoneRcv != null) {
			$this->getCore()->getPhone()->handleInCall($player, $message);
			$this->getCore()->getChatter()->send($player, $message, " §8говорит в телефон §7>");
			$this->getCore()->getTrackerModule()->message($player, $message, 7, "[PHONE]");
			return;
		}

		if ($player->muted) {
			$player->sendMessage("Вы не можете писать в чат, так как вам выдали мут.");
			return;
		}
		
		if ($message[0] == GameChat::GLOBAL_CHAT_SIGNATURE) {
			$this->getCore()->getChatter()->sendGlobal($player, $message);
		} elseif ($message[0] == GameChat::ADMINISTRATION_CHAT_SIGNATURE) {
			$this->getCore()->getChatter()->sendForAdministration($player, $message);
		} else {
			$this->getCore()->getChatter()->send($player, $message);
			$this->getCore()->getTrackerModule()->message($player, $message, 7, "[CHAT]");
		}
	}
	
	public function quitEvent(PlayerQuitEvent $event)
	{
		$event->setQuitMessage(null);

		$player = MineParkPlayer::cast($event->getPlayer());
		$playerName = $player->getName();
		
		$this->getCore()->getApi()->sendToMessagesLog($playerName, "*** Выход из игры");
		
		if ($player->getStatesMap()->phoneRcv != null) {
			$this->getCore()->getPhone()->breakCall($event->getPlayer());
		}

		if ($this->getCore()->getTrackerModule()->isTracked($player)) {
			$this->getCore()->getTrackerModule()->disableTrack($player);
		}

		$this->getUsersSource()->updateUserQuitStatus($playerName);
	}
	
	public function preLoginEvent(PlayerPreLoginEvent $event)
	{
		$this->getCore()->getInitializer()->initialize($event->getPlayer());
	}
	
	public function tapEvent(PlayerInteractEvent $event)
	{
		$this->ignoreTapForItems($event);

		$player = MineParkPlayer::cast($event->getPlayer());
		$block = $event->getBlock();

		if (!$player->getStatesMap()->auth) {
			return $event->setCancelled();
		}

		if (!$this->isCanActivate($event)) {
			return;
		}

		$this->getCore()->getInitializer()->checkInventoryItems($player);
		
		$this->getCore()->getOrganisationsModule()->shop->tap($event);
		
		//fix of SignChangeEvent bug
		if (self::ENABLE_SIGN_EMULATION) {
			if ($block instanceof Sign or $block instanceof SignPost or $block instanceof WallSign) {
				$ev = new FixSignEvent($event);
				$this->signChangeEvent($ev->getEvent());
			}
		}
	}
	
	public function signChangeEvent($ev)
	{
		$this->getCore()->getOrganisationsModule()->shop->sign($ev);
		$this->getCore()->getOrganisationsModule()->workers->sign($ev);
	}
	
	public function signSetEvent(SignChangeEvent $e)
	{
		if(!$e->getPlayer()->getStatesMap()->auth) {
			$e->setCancelled();
		}
	}
	
	public function blockPlaceEvent(BlockPlaceEvent $event)
	{
		$this->checkBlockSet($event);
	}
	
	public function blockBreakEvent(BlockBreakEvent $event)
	{
		$this->checkBlockSet($event);
	}
	
	public function playerDmgEvent(EntityDamageEvent $event)
	{
		$cancel = false;

		if(($event instanceof EntityDamageByEntityEvent and $event->getEntity() instanceof Painting) 
			and ($event->getDamager() instanceof MineParkPlayer and !$event->getDamager()->isOp())) {
			$cancel = true;
		}

		if($event instanceof EntityDamageByEntityEvent and $event->getDamager() instanceof MineParkPlayer and $event->getEntity() instanceof MineParkPlayer) {
			$cancel = $this->getCore()->getDamager()->kick($event->getEntity(), $event->getDamager());
		}

		if(($event->getEntity()->getHealth() - $event->getFinalDamage()) <= 0 and $event->getEntity() instanceof MineParkPlayer and $event->getEntity()->getGamemode() != 1) {
			$damager = $event instanceof EntityDamageByEntityEvent ? $event->getDamager() : null;
			$cancel = $this->getCore()->getDamager()->kill($event->getEntity(), $damager);
		}
		
		if($cancel) {
			$event->setCancelled();
		}
	}

	public function chunkLoadEvent(ChunkLoadEvent $event) 
	{
		if ($event->isNewChunk()) {
			$x = $event->getChunk()->getX();
			$z = $event->getChunk()->getZ();
			
			$event->getLevel()->unloadChunk($x, $z);
		}
	}

	public function blockBurnEvent(BlockBurnEvent $event)
	{
		$event->setCancelled();
	}

	private function checkBlockSet(BlockEvent $event)
	{
		$player = $event->getPlayer();

		if (!$player->getStatesMap()->auth) {
			$event->setCancelled();
			return;
		}

		if ($player->isOp()) {
			$event->setCancelled(false);
		}

		if ($player->getProfile()->builder) {
			$event->setCancelled(false);
		}

		if (!$this->getCore()->getWorldProtector()->isInRange($event->getBlock())) {
			$event->setCancelled(false);
		}
	}

	private function ignoreTapForItems(PlayerInteractEvent $event)
	{
		$itemId = $event->getPlayer()->getInventory()->getItemInHand()->getId();
		
		// обязательно при переписе кора использовать здесь константы из Item
		$items = [269, 273, 277, 321, 199, 284, 325];

		if (in_array($itemId, $items) and !$event->getPlayer()->isOp()) {
			$event->setCancelled();
		}
	}

	private function isCanActivate(PlayerInteractEvent $event) : bool
	{
		$currentTime = time();

		if ($currentTime - $event->getPlayer()->getStatesMap()->lastTap > 2) {
			$event->getPlayer()->getStatesMap()->lastTap = $currentTime;

			return true;
		}
		
		return false;
	}

	private function getUsersSource() : UsersSource
	{
		return $this->getCore()->getMDC()->getSource(UsersSource::ROUTE);
	}
}
?>
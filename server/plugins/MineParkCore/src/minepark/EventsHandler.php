<?php
namespace minepark;

use minepark\player\Chatter;
use minepark\utils\FixSignEvent;
use minepark\player\ImplementedPlayer;

use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use pocketmine\event\Listener;
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

class EventsHandler implements Listener
{
    public const ENABLE_SIGN_EMULATION = false;

	public function getCore() : Core
	{
		return Core::getActive();
	}
    
    public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(ImplementedPlayer::class);
	}
	
	public function commandEvent(PlayerCommandPreprocessEvent $event)
	{
		if(!$event->getPlayer()->auth) {
			$this->getCore()->getAuthModule()->login($event->getPlayer(), $event->getMessage());
			$event->setCancelled();
			return;
		}

		$this->getCore()->getApi()->sendToMessagesLog($event->getPlayer()->getName(), $event->getMessage());
		$this->getCore()->getCommandsHandler()->execute($event->getPlayer(), $event->getMessage(), $event);
		$this->getCore()->getOrganisationsModule()->getCommandHandler()->execute($event->getPlayer(), $event->getMessage(), $event);
	}
	
	public function joinEvent(PlayerJoinEvent $event)
	{
		$this->getCore()->getApi()->sendToMessagesLog($event->getPlayer()->getName(), "Вход осуществлен ***");

		$this->getCore()->getAuthModule()->preLogin($event->getPlayer());
		
		$this->getCore()->getInitializer()->join($event->getPlayer());

		$this->getCore()->getPhone()->init($event->getPlayer());

		$this->getCore()->getTrackerModule()->enableTrack($event->getPlayer());

		$event->setJoinMessage(null);
	}
	
	public function chatEvent(PlayerChatEvent $e)
	{
		$e->setCancelled();

		if($e->getPlayer()->phoneRcv != null) {
			$this->getCore()->getPhone()->handleInCall($e->getPlayer(), $e->getMessage());
			$this->getCore()->getChatter()->send($e->getPlayer(), $e->getMessage(), " §8говорит в телефон §7>");
			$this->getCore()->getTrackerModule()->message($e->getPlayer(), $e->getMessage(), 7, "[PHONE]");
			return;
		}

		if ($e->getPlayer()->muted) {
			$e->getPlayer()->sendMessage("Вы не можете писать в чат, так как вам выдали мут.");
			return;
		}
		
		if($e->getMessage()[0] == Chatter::GLOBAL_CHAT_SIGNATURE) {
			$this->getCore()->getChatter()->sendGlobal($e->getPlayer(), $e->getMessage());
		} else {
			$this->getCore()->getChatter()->send($e->getPlayer(), $e->getMessage());
			$this->getCore()->getTrackerModule()->message($e->getPlayer(), $e->getMessage(), 7, "[CHAT]");
		}
	}
	
	public function quitEvent(PlayerQuitEvent $e)
	{
		$e->setQuitMessage(null);
		
		$this->getCore()->getApi()->sendToMessagesLog($e->getPlayer()->getName(), "*** Выход из игры");
		
		if($e->getPlayer()->phoneRcv != null) {
			$this->getCore()->getPhone()->breakCall($e->getPlayer());
		}

		if ($this->getCore()->getTrackerModule()->isTracked($e->getPlayer())) {
			$this->getCore()->getTrackerModule()->disableTrack($e->getPlayer());
		}
	}
	
	public function preLoginEvent(PlayerPreLoginEvent $event)
	{
		$this->getCore()->getInitializer()->initialize($event->getPlayer());
	}
	
	public function tapEvent(PlayerInteractEvent $event)
	{
		if(!$this->isCanActivate($event)) {
			return;
		}

		if((!$event->getPlayer()->auth or $event->getBlock()->getId() == 71)
			or (($event->getPlayer()->getInventory()->getItemInHand()->getId() == 259) and !$event->getPlayer()->isOp())) {
				$event->setCancelled();
		}
		
		$this->ignoreTapForItems($event);
		
		$this->getCore()->getOrganisationsModule()->shop->tap($event);
		
		//fix of SignChangeEvent bug
		if(self::ENABLE_SIGN_EMULATION) {
			$block = $event->getBlock();
			if($block instanceof Sign or $block instanceof SignPost or $block instanceof WallSign) {
				$ev = new FixSignEvent($event);
				$this->signChangeEvent($ev->getEvent());
			}
		}
	}

	private function ignoreTapForItems(PlayerInteractEvent $event)
	{
		$itemId = $event->getPlayer()->getInventory()->getItemInHand()->getId();
		
		$items = [269, 273, 277, 321, 199, 284, 325];

		if(in_array($itemId, $items) and !$event->getPlayer()->isOp()) {
			$event->setCancelled();
		}
	}
	
	public function signChangeEvent($ev)
	{
		$this->getCore()->getOrganisationsModule()->shop->sign($ev);
		$this->getCore()->getOrganisationsModule()->workers->sign($ev);
	}
	
	public function signSetEvent(SignChangeEvent $e)
	{
		if(!$e->getPlayer()->auth) {
			$e->setCancelled();
		}
	}
	
	public function blockPlaceEvent(BlockPlaceEvent $e)
	{
		if(!$e->getPlayer()->auth) {
			$e->setCancelled();
		}
	}
	
	public function blockBreakEvent(BlockBreakEvent $e)
	{
		if(!$e->getPlayer()->auth) {
			$e->setCancelled();
		}
	}
	
	public function playerDmgEvent(EntityDamageEvent $event)
	{
		$cancel = false;

		if($event instanceof EntityDamageByEntityEvent and $event->getDamager() instanceof Player and $event->getEntity() instanceof Player) {
			$cancel = $this->getCore()->getDamager()->kick($event->getEntity(), $event->getDamager());
		}

		if(($event->getEntity()->getHealth() - $event->getFinalDamage()) <= 0 and $event->getEntity() instanceof Player and $event->getEntity()->getGamemode() != 1) {
			$damager = $event instanceof EntityDamageByEntityEvent ? $event->getDamager() : null;
			$cancel = $this->getCore()->getDamager()->kill($event->getEntity(), $damager);
		}
		
		if($cancel) {
			$event->setCancelled();
		}
	}

	private function isCanActivate(PlayerInteractEvent $event) : bool
	{
		$currentTime = time();

		if($currentTime - $event->getPlayer()->lastTap > 2) {
			$event->getPlayer()->lastTap = $currentTime;

			return true;
		}
		
		return false;
	}
}
?>
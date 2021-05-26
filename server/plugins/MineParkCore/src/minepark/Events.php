<?php
namespace minepark;

use minepark\defaults\EventList;
use pocketmine\event\Event;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
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
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class Events implements Listener
{
    private static array $eventsRegistry = [];

    public static function initializeAll()
    {
        $eventsList = [
            EventList::PLAYER_CREATION_EVENT,
            EventList::PLAYER_COMMAND_PREPROCESS_EVENT,
            EventList::PLAYER_JOIN_EVENT,
            EventList::PLAYER_CHAT_EVENT,
            EventList::PLAYER_QUIT_EVENT,
            EventList::PLAYER_PRE_LOGIN_EVENT,
            EventList::PLAYER_INTERACT_EVENT,
            EventList::SIGN_CHANGE_EVENT,
            EventList::DATA_PACKET_RECEIVE_EVENT,
            EventList::BLOCK_PLACE_EVENT,
            EventList::BLOCK_BREAK_EVENT,
            EventList::BLOCK_BURN_EVENT,
            EventList::ENTITY_DAMAGE_EVENT,
            EventList::CHUNK_LOAD_EVENT,
            EventList::INVENTORY_TRANSACTION_EVENT
        ];

        foreach($eventsList as $listEventId) {
            self::$eventsRegistry[$listEventId] = [];
        }
    }

    public static function registerEvent(int $listEventId, callable $callable)
    {
        array_push(self::$eventsRegistry[$listEventId], $callable);
    }

    public static function callEvent(int $listEventId, Event $event)
    {
        foreach(self::$eventsRegistry[$listEventId] as $callable) {
            call_user_func_array($callable, [$event]);
        }
    }

    /*
        Local event callers
    */

    public function callPlayerCreationEvent(PlayerCreationEvent $event)
    {
        self::callEvent(EventList::PLAYER_CREATION_EVENT, $event);
    }
    
    public function callPlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event)
    {
        self::callEvent(EventList::PLAYER_COMMAND_PREPROCESS_EVENT, $event);
    }
    
    public function callPlayerJoinEvent(PlayerJoinEvent $event)
    {
        self::callEvent(EventList::PLAYER_JOIN_EVENT, $event);
    }

    public function callPlayerChatEvent(PlayerChatEvent $event)
    {
        self::callEvent(EventList::PLAYER_CHAT_EVENT, $event);
    }
    
    public function callPlayerQuitEvent(PlayerQuitEvent $event)
    {
        self::callEvent(EventList::PLAYER_QUIT_EVENT, $event);
    }
    
    public function callPlayerPreLoginEvent(PlayerPreLoginEvent $event)
    {
        self::callEvent(EventList::PLAYER_PRE_LOGIN_EVENT, $event);
    }
    
    public function callPlayerInteractEvent(PlayerInteractEvent $event)
    {
        self::callEvent(EventList::PLAYER_INTERACT_EVENT, $event);
    }
    
    public function callSignChangeEvent(SignChangeEvent $event)
    {
        self::callEvent(EventList::SIGN_CHANGE_EVENT, $event);
    }
    
    public function callDataPacketReceiveEvent(DataPacketReceiveEvent $event)
    {
        self::callEvent(EventList::DATA_PACKET_RECEIVE_EVENT, $event);
    }
    
    public function callBlockPlaceEvent(BlockPlaceEvent $event)
    {
        self::callEvent(EventList::BLOCK_PLACE_EVENT, $event);
    }
    
    public function callBlockBreakEvent(BlockBreakEvent $event)
    {
        self::callEvent(EventList::BLOCK_BREAK_EVENT, $event);
    }
    
    public function callEntityDamageEvent(EntityDamageEvent $event)
    {
        self::callEvent(EventList::ENTITY_DAMAGE_EVENT, $event);
    }

    public function callChunkLoadEvent(ChunkLoadEvent $event) 
    {
        self::callEvent(EventList::CHUNK_LOAD_EVENT, $event);
    }

    public function callBlockBurnEvent(BlockBurnEvent $event)
    {
        self::callEvent(EventList::BLOCK_BURN_EVENT, $event);
    }

    public function callInventoryTransactionEvent(InventoryTransactionEvent $event)
    {
        self::callEvent(EventList::INVENTORY_TRANSACTION_EVENT, $event);
    }
}
?>
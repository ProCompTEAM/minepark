<?php
/* 
Все права на данный плагин пренадлежат его автору!
Дата начала создания плагина: 23.01.2019
Я ВКОНТАКТЕ: https://vk.com/tnull2
*/
namespace SmartRealty;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use pocketmine\tile\Sign;
use pocketmine\command\CommandSender;
use pocketmine\command\Command; 

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

class SmartRealty extends PluginBase implements Listener
{
    public $economy;

    private $levels;

    public $property;

    public $signev;
    
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        if(!file_exists($this->getDirectory())) {
            mkdir($this->getDirectory());
        }
        
        if(!file_exists($this->getDirectory() . "levels.txt")) {
            file_put_contents($this->getDirectory() . "levels.txt", strtolower($this->getServer()->getDefaultLevel()->getName()));
        }
                
        foreach(explode(", ", file_get_contents($this->getDirectory() . "levels.txt")) as $l) {
            $this->levels[strtolower($l)] = true;
        }
        
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        
        $this->property = new Property($this);
        
        $this->signev = false;
    }
    
    public function getDirectory()
    {
        return $this->getDataFolder();
    }
    
    public function getProperty() : Property
    {
        return $this->property;
    }
    
    public function setPermission(Player $player, $perm, $status = true)
    {
        $player->addAttachment($this, $perm, $status);
    }
    
    public function joinEvent(PlayerJoinEvent $e)
    {
        $this->getProperty()->updatePlayerProperty($e->getPlayer());
    }
    
    public function preloginEvent(PlayerPreLoginEvent $e)
    {
        $e->getPlayer()->property = array();
        $e->getPlayer()->propPos1 = null; 
        $e->getPlayer()->propPos2 = null;
    }
    
    public function tapEvent(PlayerInteractEvent $e)
    {	
        if(!isset($this->levels[strtolower($e->getPlayer()->getWorld()->getName())])) {
            return;
        }
    
        $this->getProperty()->tap($e);

        if($this->signev) { //fix of SignChangeEvent bug
            if($e->getBlock() instanceof Sign or $e->getBlock() instanceof SignPost or $e->getBlock() instanceof WallSign) {
                $ev = new FixSignEvent($e);
                $this->signChangeEvent($ev->getEvent());
            }
        }
    }
    
    public function signChangeEvent($ev)
    {
        $this->getProperty()->sign($ev);
    }
    
    public function blockPlaceEvent(BlockPlaceEvent $e)
    {
        if($e->getPlayer()->isBuilder()) {
            return;
        }
        
        if(!isset($this->levels[strtolower($e->getPlayer()->getWorld()->getName())])) {
            return;
        }
        
        $this->getProperty()->block($e, "place");
    }
    
    public function blockBreakEvent(BlockBreakEvent $e)
    {
        if($e->getPlayer()->isBuilder()) {
            return;
        }

        if(!isset($this->levels[strtolower($e->getPlayer()->getWorld()->getName())])) {
            return;
        }
        
        $this->getProperty()->block($e, "break");
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $cmds) : bool
    {
        if($sender instanceof Player and !isset($this->levels[strtolower($sender->getWorld()->getName())])) {
            return false;
        }
        
        if($cmd == "realt") {
            $this->getProperty()->command($sender, $cmds);
        }
        
        return true;
    }
}
?>
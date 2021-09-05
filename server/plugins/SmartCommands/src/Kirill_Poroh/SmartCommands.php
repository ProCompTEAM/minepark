<?php

declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;


class SmartCommands extends PluginBase implements Listener 
{	
    private $command;

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->getLogger()->info("§eSmartCommands MCPE!");
        
        if(!file_exists($this->getDirectory())) {
            mkdir($this->getDirectory());
        }
        
        $this->command = array();

        $this->command["pos"] = new PosCommand($this);
        $this->command["getpos"] = new PosCommand($this);
        $this->command["mute"] = new MuteCommand($this);
        $this->command["unmute"] = new MuteCommand($this);
        $this->command["coffee"] = new CoffeeCommand($this);
        $this->command["cc"] = new ClearCommand($this);
        $this->command["v"] = new VanishCommand($this);
        $this->command["freeze"] = new FreezeCommand($this);
        $this->command["unfreeze"] = new FreezeCommand($this);
        $this->command["burn"] = new BurnCommand($this);
        $this->command["see"] = new SeeCommand($this);
        $this->command["timeban"] = new TBCommand($this);
        $this->command["free"] = new FreeCommand($this);
    }
    
    public function getDirectory()
    {
        return $this->getDataFolder();
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, $args) : bool
    {
        if($sender instanceof Player)
        {
            if (isset($this->command[$command->getName()]))
            {
                if(!$this->command[$command->getName()]->run($command->getName(), $args, $sender)) {
                    $sender->sendMessage("§6Друг, чтобы иметь доступ к этой команде, необходимо купить подходящий донат: /donate");
                    return false;
                } else {
                    return true;
                }
            }
        }
        else {
            $sender->sendMessage("§cКоманды плагина можно выполнять только от имени игрока!");
            
            return false;
        }
        
        return false;
    }
    
    public function getCommand($name)
    {
        return $this->command[$name];
    }
    
    public function getCommands()
    {
        return $this->command;
    }
    
    public function eJoin(PlayerJoinEvent $e)
    {
        if(isset($this->command["timeban"])) {
            $this->command["timeban"]->checkBanned($e->getPlayer());
            
            return;
        }
        
        if(isset($this->command["mute"]) and $this->command["mute"]->isMuted($e->getPlayer())) {
            $e->getPlayer()->muted = true;
        } else {
            $e->getPlayer()->muted = false;
        }
    }
    
    public function eChat(PlayerChatEvent $e)
    {
        if(isset($this->command["timeban"])) {
            $this->command["timeban"]->checkBanned($e->getPlayer());
            
            return;
        }
        
        if($e->getPlayer()->muted) {
            $e->getPlayer()->sendMessage("§6Ваш чат заблокирован администрацией ;(");
            
            $e->cancel();
        }
    }
}


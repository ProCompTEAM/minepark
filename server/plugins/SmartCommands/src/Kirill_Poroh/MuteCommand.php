<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\Player;
use pocketmine\utils\Config;

class MuteCommand
{	
    private $main;
    private $config;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
        
        $this->config = new Config($MAIN->getDirectory() . "muted.json", Config::JSON);
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "mute") 
        {
            if($player->hasPermission("sc.command.mute")) 
            {
                if(!isset($args[0])) 
                {
                    $player->sendMessage("§cФормат: /mute <ник игрока>");
                    
                    return true;
                }
                
                $p = $this->main->getServer()->getPlayer($args[0]);
                $name = ($p == null ? $args[0] : $p->getName());
                
                if($p === null) 
                {
                    $player->sendMessage("§cИгрок $name вне игры!");
                    
                    return true;
                }
                
                $p->muted = true;
                
                $this->config->set(strtolower($name));
                $this->config->save();
                
                $this->main->getServer()->broadcastMessage($player->getName() . " запретил писать в чат игроку§3 $name");
            }
            else return false;
        }
            
        if($command == "unmute") 
        {
            if($player->hasPermission("sc.command.mute"))
            {				
                if(!isset($args[0])) 
                {
                    $player->sendMessage("§cФормат: /unmute <ник игрока>");
                    
                    return true;
                }
                
                $p = $this->main->getServer()->getPlayer($args[0]);
                $name = $p->getName();
                
                if($p === null) 
                {
                    $player->sendMessage("§cИгрок $name вне игры!");
                    
                    return true;
                }
                
                $p->muted = false;
                
                $this->config->remove(strtolower($name));
                $this->config->save();
                
                $this->main->getServer()->broadcastMessage($player->getName() . " вернул право писать в чат игроку§3 $name");
            }
            else return false;
        }
        
        return true;
    }
    
    public function isMuted(Player $player)
    {
        return $this->config->exists(strtolower($player->getName()));
    }
}

<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\player\Player;

use pocketmine\scheduler\TaskScheduler;

class FreeCommand
{	
    private $main;
    
    private $modes;
    private $seconds;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
        
        $this->modes   = array();
        $this->seconds = array();

        $this->main->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "updater")), 20);
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "free") 
        {
            if($player->hasPermission("sc.command.free")) 
            {
                if(count($args) < 3) 
                {
                    $player->sendMessage("§cФормат: /free <ник игрока> <fly | gm> <время/секунд>");
                    
                    return true;
                }
                
                $mode = $args[1];
                $seconds = $args[2];
                
                if($seconds > 0)
                {	
                    $p = $this->main->getServer()->getPlayer($args[0]);
                    $name = ($p == null ? $args[0] : $p->getName());
                    
                    if($p !== null)
                    {
                        $this->modes[$p->getName()] = $mode;
                        $this->seconds[$p->getName()] = $seconds;
                        
                        if($mode == "gm") $p->setGamemode(1);
                        if($mode == "fly") $p->setAllowFlight(true);
                        
                        $p->sendMessage("§aВам выдан§9 $mode §dна $seconds §aсекунд %)");
                        $player->sendMessage("§aВы выдали§9 $mode §dна $seconds §aсекунд для§3 $name");
                    }
                    else $player->sendMessage("§cИгрок offline!");
                }
                else $player->sendMessage("§cПродолжительнасть должна быть в числовом значении секунд!");
            }
            else return false;
        }
        
        return true;
    }
    
    public function updater()
    {
        foreach($this->modes as $name => $mode)
        {
            $p = $this->main->getServer()->getPlayer($name);
            
            if($p !== null)
            {
                $this->seconds[$name]--;
                
                if($this->seconds[$name] < 1)
                {
                    if($mode == "gm") $p->setGamemode(0);
                    if($mode == "fly") $p->setAllowFlight(false);
                    
                    unset($this->modes[$name]);
                    unset($this->seconds[$name]);
                    
                    $p->sendMessage("§6Период §a$mode §6окончен §e:/");
                }
                else $p->sendTip("§l§c" . $this->seconds[$name]);
            }
            else
            {
                unset($this->modes[$name]);
                unset($this->seconds[$name]);
            }
        }
    }
}

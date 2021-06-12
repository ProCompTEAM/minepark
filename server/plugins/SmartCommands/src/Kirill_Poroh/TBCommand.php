<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\player\Player;
use pocketmine\utils\Config;

class TBCommand
{	
    private $main;
    private $config;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
        
        $this->config = new Config($MAIN->getDirectory() . "banned.json", Config::JSON);
    }
    
    public function run($command, $args, Player $player)
    {
        if($command == "timeban") 
        {
            if($player->hasPermission("sc.command.timeban")) 
            {
                if(count($args) < 1) 
                {
                    $player->sendMessage("§cФормат: /timeban <ник игрока> <время/мин | сутки | неделя>");
                    
                    return true;
                }
                
                elseif(count($args) == 1)
                {
                    $name = $args[0];
                    
                    if($this->config->exists(strtolower($name)))
                    {
                        $this->config->remove(strtolower($name));
                        
                        $this->config->save();
                        
                        $this->main->getServer()->broadcastMessage("§b" . $player->getName() . " §aразблокировал досрочно§6 $name");
                    }
                    else $player->sendMessage("§cИгрок не был заблокирован через /timeban ранее!");
                }
                
                else
                {
                    $p = $this->main->getServer()->getPlayer($args[0]);
                    $name = ($p == null ? $args[0] : $p->getName());
                    $per = $args[1];
                    
                    if($per == "сутки") $per = 1440;
                    if($per == "неделя") $per = 1440 * 7;
                    
                    if(is_numeric($per) and $per > 0)
                    {
                        if($p !== null) $player->close("", "§cБан выдан от " . $player->getName());
                        
                        $this->config->set(strtolower($name), $this->format($per));
                        $this->config->save();
                        
                        $this->main->getServer()->broadcastMessage("§e" . $player->getName() . " §dзаблокировал игрока§6 $name §dна§3 $per §dминут");
                    }
                    else $player->sendMessage("§cДлительность не является числом. Вариации: <значение минут>/сутки/неделя");
                }
            }
            else return false;
        }
        
        return true;
    }
    
    private function format($per)
    {
        return time() . ":" . $per;
    }
    
    public function checkBanned(Player $player)
    {
        $name = $player->getName();
        
        if($this->config->exists(strtolower($name)))
        {
            $data = explode(":", $this->config->get(strtolower($name)));
            
            $time = $data[0];
            $per = $data[1];
            
            if(($per - round((time() - $time) / 60) + 1) < 1) 
            {
                $this->config->remove(strtolower($name));
                
                $this->config->save();
            }
            else $player->close("", "§3Вы заблокированы на этом сервере. Установленный период: $per минут.");
        }
    }
}

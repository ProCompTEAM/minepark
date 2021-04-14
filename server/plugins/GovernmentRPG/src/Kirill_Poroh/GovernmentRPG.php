<?php
declare(strict_types = 1);

namespace Kirill_Poroh;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;

use pocketmine\block\Block;


class GovernmentRPG extends PluginBase implements Listener 
{
    private $players;
    private $law;

    private $current_law;
    
    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        $this->getLogger()->info("MCRPG.RU ядро by Kirill Poroh");
        
        $this->players = array();
        
        $this->fillList();
        
        //current task settings
        $this->current_law = array(); 
        $this->current_law["text"] = null; 
        $this->current_law["index"] = 0; 
        $this->current_law["accepts"] = 0;
    }
    
    public function onDisable()
    {
        
    }
    
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        if($command->getName() == "law")
        {
            $sender->sendMessage("§6Представленные законы установлены парламентом и периодически изменяются!");
            $sender->sendMessage("§fЗакон §b№1§f: " . $this->law[0]);
            $sender->sendMessage("§fЗакон §b№2§f: " . $this->law[1]);
            $sender->sendMessage("§fЗакон §b№3§f: " . $this->law[2]);
            $sender->sendMessage("§fЗакон §b№4§f: " . $this->law[3]);
            $sender->sendMessage("§fЗакон §b№5§f: " . $this->law[4]);
            return true;
        }
        elseif($command->getName() == "gov")
        {
            $sc = isset($args[0]) ? $args[0] : "";
            
            if($sc == "join")
            {
                if($sender->isOp() or $sender->hasPermission("mcrpg.gov.creator"))
                {
                    $players = $this->getPlayers($sender->getPosition(), 8);
                    
                    if(count($players) == 3)
                    {	
                        $this->createList($players);
                        
                        $this->getServer()->broadcastMessage("§fГлава §bЦИК §fутвердил §bновый состав §fпарламента из 3 человек:");
                        
                        foreach($players as $player)
                        {
                            $name = (isset($player->fullname) ? $player->fullname : $player->getName());
                            
                            $this->players[strtolower($player->getName())] = $player;
                            
                            $this->getServer()->broadcastMessage("§f -§b $name");
                        }
                    }
                    else $sender->sendMessage("§cВ радиусе 8 блоков должны быть ровно ТРОЕ членов парламента (и Вы)!");
                }
                else $sender->sendMessage("§cСоздавать парламент может только высшая администрация!");
            }
            
            elseif($sc == "list")
            {
                $sender->sendMessage("§dЧлены парламента: ");
                
                foreach($this->players as $name => $player)
                {
                    $sender->sendMessage("§3 - §f" . $name . " : " . (($player == "") ? "§coffline" : "§aonline"));
                }
            }
            
            // // // // // PART I // // // // //
            elseif($sc == "setlaw")
            {
                if($this->isJoined($sender))
                {
                    $players = $this->getPlayers($sender->getPosition());
                        
                    if(count($this->getOnlinePlayers()) == count($this->players))
                    {
                        if(isset($args[1]) and $args[1] > 0 and $args[1] < 6)
                        {
                            if(isset($args[2]))
                            {
                                $text = implode(" ", array_slice($args, 2));
                                
                                if(mb_strlen($text) >= 10)
                                {
                                    $name = (isset($sender->fullname) ? $sender->fullname : $sender->getName());
                                    
                                    $this->current_law["text"] = $text; 
                                    $this->current_law["index"] = $args[1] - 1;
                                    $this->current_law["accepts"] = 0; 
                                    
                                    $oldlaw = $this->law[ $this->current_law["index"] ];
                                    
                                    foreach($this->getOnlinePlayers() as $player)
                                    {
                                        $player->sendMessage("§b$name §fпредлагает заменить закон §2'$oldlaw' §6→ §a'$text'");
                                        $player->sendMessage("§fЕсли Вы с ним согласны, пишите: §a/gov accept");
                                        $player->sendMessage("§fЕсли Вы с ним §cне §fсогласны, пишите: §6/gov deny");
                                    }
                                }
                                else $sender->sendMessage("§cТекст закона должен быть более 10 символов!");
                            }
                            else $sender->sendMessage("§cТекст закона отсутсвует!");
                        }
                        else $sender->sendMessage("§cНомер закона должен быть от 1 до 5!");
                    }
                    else $sender->sendMessage("§cНа заседании менее 3 человек из парламента!");
                }
                else $sender->sendMessage("§cВы не член парламента!");
            }
            
            elseif($sc == "deny")
            {
                if($this->isJoined($sender))
                {
                    if($this->current_law["text"] != null)
                    {
                        foreach($this->getOnlinePlayers() as $player)
                        {
                            $player->sendMessage((isset($player->fullname) ? $player->fullname : $player->getName()) . "§6 голосует против!");
                        }
                    }
                    else $sender->sendMessage("§cВ данный момент законопроектов предложено не было!");
                }
                else $sender->sendMessage("§cВы не член парламента!");
            }
            
            elseif($sc == "accept")
            {
                if($this->isJoined($sender))
                {
                    if($this->current_law["text"] != null)
                    {
                        foreach($this->getOnlinePlayers() as $player)
                        {
                            $player->sendMessage((isset($player->fullname) ? $player->fullname : $player->getName()) . "§a поддерживает законопроект!");
                            
                            $this->current_law["accepts"]++;
                            
                            if($this->current_law["accepts"] == 3)
                            {
                                $old = $this->law[ $this->current_law["index"] ];
                                $cur = $this->current_law["text"];
                                
                                $this->getServer()->broadcastMessage("§bЦИК §fутвердил новый закон §3'$old' §6→ §b'$cur'");
                                
                                $this->law[ $this->current_law["index"] ] = $this->current_law["text"];
                                
                                $this->current_law["text"] = null; 
                                $this->current_law["index"] = 0; 
                                $this->current_law["accepts"] = 0;
                                
                                $this->createLaw($this->law);
                            }
                        }
                    }
                    else $sender->sendMessage("§cВ данный момент законопроектов предложено не было!");
                }
                else $sender->sendMessage("§cВы не член парламента!");
            } else {
                $sender->sendMessage("§eНеправильное использование данной команды!");
                $sender->sendMessage("§cЕсть подкоманды: §ejoin, list, deny, setlaw, accept");
            }
                
            return true;
        }
        
        return false;
    }
    
    public function playerJoinEvent(PlayerJoinEvent $e)
    {
        if(isset($this->players[strtolower($e->getPlayer()->getName())])
            and $this->players[strtolower($e->getPlayer()->getName())] == "")
                $this->players[strtolower($e->getPlayer()->getName())] = $e->getPlayer();
    }
    
    public function playerQuitEvent(PlayerQuitEvent $e)
    {
        if(isset($this->players[strtolower($e->getPlayer()->getName())]))
            $this->players[strtolower($e->getPlayer()->getName())] = null;
    }
    
    
    //   F U N C T I O N S
    
    public function getDirectory()
    {
        @mkdir("core_data/");
        return "core_data/";
    }
    
    public function getPrefix()
    {
        return "§bПРАВИТЕЛЬСТВО §a> ";
    }
    
    public function getPlayers(Position $pos, int $rad = 16)
    {
        $players = array();
        
        foreach($this->getServer()->getOnlinePlayers() as $player)
        {
            if($player->distance($pos) <= $rad and $player->getPosition() != $pos) array_push($players, $player);
        }
        
        return $players;
    }
    
    
    //создать список членов парламента
    public function createList(array $players)
    {
        $this->players = array();
        
        $path = $this->getDirectory() . "government.auto.txt";
        
        if(file_exists($path)) unlink($path);
        
        foreach($players as $player) 
        {
            $this->players[strtolower($player->getName())] = $player;
            
            file_put_contents($path, strtolower($player->getName()) . "\r\n", FILE_APPEND);
        }
    }
    
    //восстановить список членов парламента
    public function fillList()
    {	
        $path = $this->getDirectory() . "government.auto.txt";
        
        if(file_exists($path))
        {	
            $this->getServer()->getLogger()->info("Загрузка списка членов парламента...");
            
            $content = file_get_contents($path);
            $list = explode("\r\n", $content);
            unset($list[count($list) - 1]);
            
            $this->getServer()->getLogger()->info("Найдены в базе: " . implode(", ", $list));
            
            foreach($list as $name) 
            {
                $this->players[$name] = "";
            }
        }
        
        $this->fillLaw();
    }
    
    public function getOnlinePlayers() : array
    {
        $players = array();
        
        foreach($this->players as $player)
        {
            if($player != "") array_push($players, $player);
        }
        
        return $players;
    }
    
    public function isJoined(Player $player) : bool
    {
        return isset( $this->players[strtolower($player->getName())] );
    }
    
    public function isOnline(Player $player) : bool
    {
        foreach($this->getOnlinePlayers() as $p)
        {
            if($player->getName() == $p->getName())  return true;
        }
        
        return false;
    }
    
    //создать список законов
    public function createLaw(array $lines)
    {
        $this->law = array();
        
        $path = $this->getDirectory() . "government-law.auto.txt";
        
        if(file_exists($path)) unlink($path);
        
        foreach($lines as $line) file_put_contents($path, $line . "\r\n", FILE_APPEND);
    }
    
    
    //восстановить список законов
    public function fillLaw()
    {
        $this->law = array();
        
        $path = $this->getDirectory() . "government-law.auto.txt";
        
        if(file_exists($path))
        {	
            $content = file_get_contents($path);
            $this->law = explode("\r\n", $content);
        }
        else
        {
            $this->law[0] = "в данный момент не установлен!";
            $this->law[1] = "в данный момент не установлен!";
            $this->law[2] = "в данный момент не установлен!";
            $this->law[3] = "в данный момент не установлен!";
            $this->law[4] = "в данный момент не установлен!";
        }
    }
}

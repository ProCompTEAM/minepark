<?php  
namespace lifts; 

use lifts\Run;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\player\Player;
use pocketmine\block\Block;
use pocketmine\math\Vector3; 
use pocketmine\event\Listener;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use pocketmine\command\Command; 
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase; 
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;

class Control extends PluginBase implements Listener 
{
    public function onEnable() : void
    { 
        if(!file_exists($this->getDefaultDir())) mkdir($this->getDefaultDir()); 
        if(!file_exists($this->getDefaultDir()."speed.txt")) file_put_contents($this->getDefaultDir()."speed.txt", "2"); 
        $this->getServer()->broadcastMessage(TextFormat::GREEN."Теперь на Вашем сервере есть лифты!"); 
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        $this->run = new Run($this); 
        $this->lifts = array(); 

        $result = file_get_contents($this->getDefaultDir()."speed.txt"); 
        if($result == "1") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 40); 
        elseif($result == "3") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 10); 
        elseif($result == "4") $this->getScheduler()->scheduleRepeatingTask(new Work($this), 5); 
        else $this->getScheduler()->scheduleRepeatingTask(new Work($this), 20); 
    } 
    
    public function getDefaultDir() 
    { 
        return $this->getDataFolder(); 
    } 
    
    public function move(Position $pos) 
    { 
        $x = $pos->getX();
        $y = $pos->getY()-1;
        $z = $pos->getZ();
        $w = $pos->getWorld();

        $allpos = array( 
            new Position($x, $y, $z, $w), 
            new Position($x+1, $y, $z, $w), 
            new Position($x, $y, $z+1, $w), 
            new Position($x-1, $y, $z, $w), 
            new Position($x, $y, $z-1, $w), 
            new Position($x+1, $y, $z+1, $w), 
            new Position($x-1, $y, $z-1, $w), 
            new Position($x-1, $y, $z+1, $w), 
            new Position($x+1, $y, $z-1, $w)); 

        foreach($allpos as $i) { 
            $w->setBlock(new Vector3($i->getX(), $i->getY(), $i->getZ()), BlockFactory::getInstance()->get(BlockLegacyIds::QUARTZ_BLOCK));
        }
        $w->setBlock(new Vector3($x, $y, $z), BlockFactory::getInstance()->get(BlockLegacyIds::IRON_BLOCK));
        
        foreach($allpos as $i) { $w->setBlock(new Vector3($i->getX(), $i->getY()+1, $i->getZ()), BlockFactory::getInstance()->get(BlockLegacyIds::AIR)); }
        foreach($allpos as $i) { $w->setBlock(new Vector3($i->getX(), $i->getY()-1, $i->getZ()), BlockFactory::getInstance()->get(BlockLegacyIds::AIR)); }
    } 
    
    public function clear(Position $pos) 
    { 
        $x = $pos->getX();
        $y = $pos->getY()-1;
        $z = $pos->getZ();
        $w = $pos->getWorld();

        $allpos = array( 
            new Position($x, $y, $z, $w), 
            new Position($x+1, $y, $z, $w), 
            new Position($x, $y, $z+1, $w), 
            new Position($x-1, $y, $z, $w), 
            new Position($x, $y, $z-1, $w), 
            new Position($x+1, $y, $z+1, $w), 
            new Position($x-1, $y, $z-1, $w), 
            new Position($x-1, $y, $z+1, $w), 
            new Position($x+1, $y, $z-1, $w)
        );

        foreach($allpos as $i){
            $w->setBlock(new Vector3($i->getX(), $i->getY(), $i->getZ()), BlockFactory::getInstance()->get(BlockLegacyIds::AIR));
        }
    } 
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $cmds) : bool
    {
        if($cmd == "lift") { 
            if(!isset($cmds[0])) return false;

            switch($cmds[0]) { 
                case "set":

                if(!Empty($cmds[1])) {
                $this->run->create($cmds[1], $sender->getPosition()); $sender->sendMessage("LiftsSet");
                }
                else $this->showError($sender, 1);
                break;

                case "unset":
                if(!Empty($cmds[1])) {
                $this->run->remove($cmds[1]);
                $sender->sendMessage("LiftsUnset");
                }
                else $this->showError($sender, 1);
                break;

                case "reload":
                $this->run->loadAll();
                $sender->sendMessage("LiftsReload");
                break;

                case "list":
                $list = $this->run->scndr($this->getDefaultDir()."db/");
                $text = null;
                foreach($list as $item) {
                    $text .= substr($item, 0, -4)." ; ";
                }
                $sender->sendMessage("LiftsList");
                break;

                case "speed":
                if($cmds[1] > 0 and $cmds[1] < 5) {
                    $sender->sendMessage("LiftsSpeed1");
                    $sender->sendMessage("LiftsSpeed2");
                    $dir = $this->getDefaultDir()."speed.txt"; file_put_contents($dir, $cmds[1]);
                }
                else $this->showError($sender, 3);
                break;

                case "help":
                $menu = "LiftsHelp1";
                $menu .= "LiftsHelp2";
                $menu .= "LiftsHelp3";
                $menu .= "LiftsHelp4";
                $menu .= "LiftsHelp5";
                $menu .= "LiftsHelp6";
                $menu .= "LiftsHelp7";
                $menu .= "LiftsHelp8";
                $menu .= "LiftsHelp9";
                $sender->sendMessage($menu);
                break;
            } 
        }

        return true;
    } 
    
    public function onClick(PlayerInteractEvent $e) 
    { 
        $list = $this->run->getItems(); 

        $p = $e->getPlayer(); 
        $b = $e->getBlock(); 

        $x = floor($b->getPos()->getX());
        $y = floor($b->getPos()->getY());
        $z = floor($b->getPos()->getZ());
        $wname = $p->getWorld()->getDisplayName();

        $form1 = "$x:$y:$z:$wname";

        if($e->getBlock()->getId() == 42 and is_array($list)) { 
            foreach($list as $i) { 
                $x = $i->getX(); $y = $i->getY()-1; $z = $i->getZ(); 
                $wname = $i->getWorld()->getName(); $form2 = "$x:$y:$z:$wname";
                if($form1 == $form2) { 
                    $this->run->start($i, "down"); 
                    $p->sendMessage("LiftsDown"); 
                    return; 
                } 
            }
            
            $pos = new Position($b->getPos()->getX(), $b->getPos()->getY(), $b->getPos()->getZ(), $p->getWorld());
            
            for ($a=0; $a < 124; $a++) { 
                $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY()+$a+1, $pos->getZ()));
                if($block->getName() != "Air") break; 
            }

            $mpos = new Position($block->getPos()->getX(), $block->getPos()->getY()+1, $block->getPos()->getZ(), $p->getWorld());

            foreach($list as $i) { 
                if($i->getX() == $mpos->getX() and $i->getY() == $mpos->getY() and $i->getZ() == $mpos->getZ() 
                    and $i->getWorld()->getName() == $mpos->getWorld()->getDisplayName())
                { 
                    $this->run->start($mpos, "up"); $p->sendMessage("LiftsUp"); 
                } 
            }
        }
    } 
    
    public function showError(Player $p, $e_id = 0) 
    { 
        $text = "LiftsError"; 
        switch($e_id) { 
            case 1: $text = "LiftsError#1"; break; 
            case 2: $text = "LiftsError#2"; break; 
            case 3: $text = "LiftsError#3"; break; 
        } 
        $p->sendMessage("LiftsShowError"); 
    } 
    
    public function getLocalPlayers(Position $pos) 
    { 
        $plist = array(); 
        foreach($this->getServer()->getOnlinePlayers() as $p) {
            if($p->getPosition()->distance($pos) < 2) array_push($plist, $p);
        } 
        return $plist; 
    } 
} 

class Work extends Task 
{
    private Control $p;

    public function __construct(Control $plugin)
    {
        $this->p = $plugin; 
        $this->p->work = $this; 
    } 
    
    public function onRun() : void
    { 
        foreach($this->p->lifts as $key => $i) 
        { 
            if($i[4] == 1) 
            { 
                $y = $this->p->lifts[$key][1]->getY() + $this->p->lifts[$key][2]; 
                $this->p->clear($i[1]); 
                if($i[2] < 1) { 
                    $this->p->move($this->p->lifts[$key][0]); 
                    unset($this->p->lifts[$key]); 
                    return; 
                } 
                $pos = new Position($i[0]->getX(), $y,$i[0]->getZ(), $i[0]->getWorld());
                $this->p->move($pos); 
                $this->p->lifts[$key][2]--;
            } else { 
                $y = $this->p->lifts[$key][1]->getY() + $this->p->lifts[$key][2] + 1; 

                if($this->p->lifts[$key][2] == 1) $this->p->clear($i[0]); 
                if($this->p->lifts[$key][2] == $i[4]) { 
                    $posend = new Position($i[1]->getX(), $i[1]->getY()+1,$i[1]->getZ(), $i[1]->getWorld());
                    $this->p->move($posend); 
                    unset($this->p->lifts[$key]); 
                    return; 
                }

                $pos = new Position($i[0]->getX(), $y,$i[0]->getZ(), $i[0]->getWorld());
                $this->p->move($pos);  
                $this->liftControl($pos); 
                $this->p->lifts[$key][2]++;  
            } 
        } 
    } 
    
    public function reload() 
    { 
        $plugin = $this->p; 
        foreach($plugin->lifts as $key => $pos) { 
            if($plugin->lifts[$key][3] == false) { 
                $h = $pos[0]->getY() - $pos[1]->getY(); 
                $plugin->lifts[$key][2] = $h; 
                $plugin->lifts[$key][3] = true; 
                if($plugin->lifts[$key][4] == 2) { 
                    $plugin->lifts[$key][4] = $h; 
                    $plugin->lifts[$key][2] = 0; 
                } 
            } 
        } 
    } 
    
    public function liftControl(Position $pos) 
    { 
        foreach($this->p->getLocalPlayers($pos) as $p) { 
            $p->teleport(new Vector3($p->x, $p->y + 1.1, $p->z));
        } 
        
    } 
}  
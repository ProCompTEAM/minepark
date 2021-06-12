<?php
namespace lifts;

use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class Run { 
    public function __construct($control_class) 
    { 
        $this->cs = $control_class;
        $this->all = array();
        $this->loadAll();
    } 

    public function create($id, Position $pos) 
    { 
        $dir = $this->cs->getDefaultDir()."db/";
        $file = $dir.$id.".txt";
        
        $x = floor($pos->getX());
        $y = floor($pos->getY());
        $z = floor($pos->getZ());

        $wname = $pos->getWorld()->getName();
        file_put_contents($file, "$x $y $z $wname");
        $this->loadAll();
        $this->cs->move($pos, Block::get(Block::IRON_BLOCK));
    }
    
    public function remove($id) 
    { 
        $dir = $this->cs->getDefaultDir()."db/";
        $file = $dir.$id.".txt";
        if(file_exists($file)) { 
            unlink($file);
        } 
        $this->loadAll();
    } 
    
    public function loadAll() { $dir = $this->cs->getDefaultDir()."db/";
        if(!file_exists($dir)) mkdir($dir);

        $list = $this->scndr($dir);

        foreach($list as $file) { 
            $data = file_get_contents($dir.$file);
            if(!Empty(explode(" ", $data)[3])) { 
                $my = explode(" ", $data);
                array_push($this->all, new Position($my[0], $my[1], $my[2], $this->cs->getServer()->getWorldByName($my[3])));
            } 
        } 
    } 
    
    public function getItems() { 
        if(!Empty($this->all)) return $this->all;
        else return \null;
    } 
    
    public function scndr($dir, $sort = 0) 
    { 
        $list = scandir($dir, $sort);
        if (!$list) return \false;
        if ($sort == 0) unset($list[0],$list[1]);
        else unset($list[count($list)-1], $list[count($list)-1]);
        return $list;
    } 
    
    public function start(Position $pos, $get_str = "down") 
    { 
        if($get_str == "down") {
            for ($i=0; $i < 124; $i++) { 
                $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY()-$i-2, $pos->getZ()));
                if($block->getName() != "Air") break;
            } 
            
            $endpos = new Position($block->getX(), $block->getY(), $block->getZ(), $pos->getWorld());

            array_push($this->cs->lifts, array($pos, $endpos, 0, \false, 1));
            
            $this->cs->work->reload();
        } 
        else {  
            for ($i=0; $i < 124; $i++) { 
                $block = $pos->getWorld()->getBlock(new Vector3($pos->getX(), $pos->getY()-$i-2, $pos->getZ()));
                if($block->getName() != "Air") break;
            } 

            $endpos = new Position($block->getX(), $block->getY(), $block->getZ(), $pos->getWorld());
            array_push($this->cs->lifts, array($pos, $endpos, 0, \false, 2));
            $this->cs->work->reload();
        }
    } 
}  
<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\block\BlockFactory;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\Tile;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\player\Player;

use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\block\Block;
use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;

class InvseeCommand
{	
    private $main;

    public function __construct($MAIN)
    {
        $this->main = $MAIN;
    }
    
    public function run($command, $args, Player $player)
    {
        $player->sendMessage("Команда в разработке..");
        return true;
        if($command == "invsee") 
        {
            if($player->hasPermission("sc.command.invsee")) 
            {
                if(!isset($args[0])) 
                {
                    $player->sendMessage("§cФормат: /invsee <ник игрока>");
                    
                    return true;
                }
                
                $p = $this->main->getServer()->getPlayerExact($args[0]);
                $name = ($p == null ? $args[0] : $p->getName());
                
                if($p === null) 
                {
                    $player->sendMessage("§cИгрок $name вне игры!");
                    
                    return true;
                }

                $vector = $player->getLocation()->asVector3();
                $vector->x = $vector->getFloorX();
                $vector->y = $vector->getFloorY();
                $vector->z = $vector->getFloorZ();

                $chest = new Chest($player->getWorld(), $vector);
                $chest->saveNBT();
                
                foreach($player->getInventory()->getContents() as $item) 
                {
                    $chest->getInventory()->addItem($item);
                }
                
                $chest->getInventory()->invsee_player = $p;

                $player->setCurrentWindow($chest->getInventory());
                
            }
            else return false;
        }
        
        return true;
    }
    
    public function transaction($transaction)
    {
        $sender = $transaction->getPlayer();
        $target = $transaction->getInventories()[1]->invsee_player;
        
        if($this->main->getServer()->getPlayerExact($target->getName()) !== null)
        {
            $target->getInventory()->clearAll();
            
            foreach($transaction->getInventories()[1]->getContents() as $item) 
            {
                $target->getInventory()->addItem($item);
            }
            
            $target->sendTip("§8Ваш инвентарь был изменен...");
            
            $this->main->getLogger()->info($sender->getName() . " изменил предмет в инвентаре " . $target->getName());
        }
    }
    
}

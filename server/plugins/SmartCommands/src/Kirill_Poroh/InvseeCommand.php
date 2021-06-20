<?php
declare(strict_types = 1);

namespace Kirill_Poroh;

use pocketmine\player\Player;

use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\tile\Tile;

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
                
                $chestBlock = new \pocketmine\block\Chest();
                
                $player->getWorld()->setBlock(new Vector3($player->getLocation()->getX(), $player->getLocation()->getY() - 4, $player->getLocation()->getZ()), $chestBlock, true, true);
                
                $nbt = new CompoundTag("", [
                    new CompoundTag("Items", array()),
                    new StringTag("id", Tile::CHEST),
                    new IntTag("x", (int) floor($player->getLocation()->getX())),
                    new IntTag("y", (int) floor($player->getLocation()->getY() - 4)),
                    new IntTag("z", (int) floor($player->getLocation()->getZ()))
                ]);
                    
                $tile = Tile::createTile("Chest", $player->getWorld(), $nbt);
                
                foreach($player->getInventory()->getContents() as $item) 
                {
                    $tile->getInventory()->addItem($item);
                }
                
                $tile->getInventory()->invsee_player = $p;
                
                $player->addWindow($tile->getInventory());
                
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

<?php
namespace minepark\defaults;

use pocketmine\item\Item;

class ItemConstants 
{
    public static function getRestrictedItemsNonBuilder() : array
    {
        return [
            Item::ITEM_FRAME,
            Item::FILLED_MAP,
            Item::TNT
        ];
    }

    public static function getGunItemIds() : array
    {
        return [
            Item::WOODEN_SHOVEL,
            Item::STONE_SHOVEL,
            Item::GOLDEN_SHOVEL,
            Item::IRON_SHOVEL,
            Item::DIAMOND_SHOVEL,
            Item::WOODEN_HOE,
            Item::STONE_HOE,
            Item::IRON_HOE,
            Item::DIAMOND_HOE
        ];
    }
}
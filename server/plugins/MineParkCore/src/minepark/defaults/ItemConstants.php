<?php
namespace minepark\defaults;

use pocketmine\item\ItemIds;

class ItemConstants 
{
    public static function getRestrictedItemsNonBuilder() : array
    {
        return [
            ItemIds::ITEM_FRAME,
            ItemIds::FILLED_MAP,
            ItemIds::TNT
        ];
    }

    public static function getGunItemIds() : array
    {
        return [
            ItemIds::WOODEN_SHOVEL,
            ItemIds::STONE_SHOVEL,
            ItemIds::GOLDEN_SHOVEL,
            ItemIds::IRON_SHOVEL,
            ItemIds::DIAMOND_SHOVEL,
            ItemIds::WOODEN_HOE,
            ItemIds::STONE_HOE,
            ItemIds::IRON_HOE,
            ItemIds::DIAMOND_HOE
        ];
    }
}
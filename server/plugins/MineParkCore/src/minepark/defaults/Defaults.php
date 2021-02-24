<?php
namespace minepark\defaults;

use pocketmine\item\Item;

class Defaults 
{
    public const PUBLIC_CHAT_PREFIX = "§6MINE§aPARK§8.§eRU §7▶";
    public const CONTEXT_NAME = "MinePark";

    public const DEFAULT_LANGUAGE_KEY = "ru_RU";
    public const INTERNATIONAL_LANGUAGE_KEY = "en_US";

    public const SERVER_LOBBY_ADDRESS = "minepark.ru";
    public const SERVER_LOBBY_PORT = 19132;

    public static function getRestrictedItemsNonOp() : array
	{
		return [
			Item::BUCKET,
			Item::ITEM_FRAME,
			Item::PAINTING
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
?>
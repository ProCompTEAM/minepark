<?php
namespace minepark\defaults;

class Permissions
{
    public const ANYBODY = "group.all";

    public const OPERATOR = "group.operator";
    public const ADMINISTRATOR = "group.admin";

    public const VIP = "group.vip";

    public const BUILDER = "group.builder";
    public const REALTOR = "group.realtor";

    public const CUSTOM = "group.custom";

    public static function getCustomAdministratorPermissions() : array
    {
        return [
            "guns.command.use",
            "pocketmine.command.teleport",
            "pocketmine.command.gamemode",
            "pocketmine.command.effect",
            "pocketmine.command.kill.other",
            "pocketmine.command.kick",
            "pocketmine.command.gamemode",
            "pocketmine.command.ban.player",
            "pocketmine.command.ban.ip",
            "pocketmine.command.give"
        ];
    }

    public static function getCustomBuilderPermissions() : array
    {
        return [
            "pocketmine.command.time",
            "pocketmine.command.gamemode",
            "pocketmine.command.give"
        ];
    }

    public static function getCustomRealtorPermissions() : array
    {
        return [
            "pocketmine.command.time",
            "realt.creator"
        ];
    }

    public static function getCustomVipPermissions() : array
    {
        return [
            "sc.command.pos",
            "sc.command.getpos",
            "sc.command.coffee",
            "sc.command.feed",
            "sc.command.heal",
            "sc.command.cc",
            "sc.command.v",
            "sc.command.see",
            "sc.command.invsee",
            "sc.command.freeze",
            "sc.command.burn",
            "sc.command.time",
            "pocketmine.command.effect"
        ];
    }
}
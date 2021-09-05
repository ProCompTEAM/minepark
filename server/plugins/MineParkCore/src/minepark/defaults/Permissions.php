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
            "pocketmine.command.teleport",
            "pocketmine.command.gamemode",
            "pocketmine.command.effect",
            "pocketmine.command.kick",
            "pocketmine.command.ban.player",
            "pocketmine.command.ban.ip",
            "pocketmine.command.give"
        ];
    }

    public static function getCustomBuilderPermissions() : array
    {
        return [
            "pocketmine.command.time",
            "pocketmine.command.time.set",
            "pocketmine.command.time.add",
            "pocketmine.command.give"
        ];
    }

    public static function getCustomRealtorPermissions() : array
    {
        return [
            "pocketmine.command.time",
            "pocketmine.command.time.set",
            "pocketmine.command.time.add",
            "realt.creator"
        ];
    }

    public static function getCustomVipPermissions() : array
    {
        return [
            "sc.command.pos",
            "sc.command.getpos",
            "sc.command.coffee",
            "sc.command.cc",
            "sc.command.v",
            "sc.command.see",
            "sc.command.freeze",
            "sc.command.burn",
            "pocketmine.command.effect"
        ];
    }
}
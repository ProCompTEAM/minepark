<?php
namespace minepark;

class Permissions
{
    const ANYBODY = "group.all";

    const OPERATOR = "group.operator";
    const ADMINISTRATOR = "group.admin";

    const VIP = "group.vip";

    const BUILDER = "group.builder";
    const REALTOR = "group.realtor";

    const CUSTOM = "group.custom";

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
?>
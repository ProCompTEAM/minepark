<?php
namespace minepark;

class Permission 
{
    const ANYBODY = "group.all";
    const PLAYERONLY = "group.player";
    const HIGH_ADMINISTRATOR = "group.operator";
    const ADMINISTRATOR = "group.admin";
    const DONATER = "group.custom";
	const CUSTOM = "group.custom";

    const ADMINISTRATOR_BUILDER = "group.builder";
    const ADMINISTRATOR_MODERATOR = "group.moder";
    const ADMINISTRATOR_HELPER = "group.helper";
    const ADMINISTRATOR_TESTER = "group.qa";

    const DONATER_A = "group.a";
    const DONATER_B = "group.b";
    const DONATER_C = "group.c";
    const DONATER_D = "group.d";
    const DONATER_E = "group.e";
}
?>
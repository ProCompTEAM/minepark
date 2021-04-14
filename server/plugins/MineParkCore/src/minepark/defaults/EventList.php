<?php
namespace minepark\defaults;

class EventList
{
    public const PLAYER_CREATION_EVENT = 1;

    public const PLAYER_COMMAND_PREPROCESS_EVENT = 2;

    public const PLAYER_JOIN_EVENT = 3;

    public const PLAYER_CHAT_EVENT = 4;

    public const PLAYER_QUIT_EVENT = 5;

    public const PLAYER_PRE_LOGIN_EVENT = 6;

    public const PLAYER_INTERACT_EVENT = 7;

    public const SIGN_CHANGE_EVENT = 8;

    public const DATA_PACKET_RECEIVE_EVENT = 9;

    public const BLOCK_PLACE_EVENT = 10;

    public const BLOCK_BREAK_EVENT = 11;

    public const BLOCK_BURN_EVENT = 12;

    public const ENTITY_DAMAGE_EVENT = 13;

    public const CHUNK_LOAD_EVENT = 14;
}
?>
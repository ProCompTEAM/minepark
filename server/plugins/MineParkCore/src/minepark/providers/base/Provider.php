<?php
namespace minepark\providers\base;

use minepark\Core;
use pocketmine\Server;

abstract class Provider
{
    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getServer()
    {
        return Server::getInstance();
    }
}
?>
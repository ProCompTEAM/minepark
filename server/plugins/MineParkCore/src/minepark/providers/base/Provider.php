<?php
namespace minepark\providers\base;

use minepark\Core;

abstract class Provider
{
    protected function getCore()
    {
        return Core::getActive();
    }
}
?>
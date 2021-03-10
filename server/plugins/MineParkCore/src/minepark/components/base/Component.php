<?php
namespace minepark\components\base;

use minepark\Core;

abstract class Component
{
    protected function getCore()
    {
        return Core::getActive();
    }
}
?>
<?php
namespace minepark\components\base;

use minepark\Core;

abstract class Component
{
    abstract public function getAttributes() : array;

    protected function getCore()
    {
        return Core::getActive();
    }
}
?>
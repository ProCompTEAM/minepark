<?php
namespace minepark\external\service\script;

use minepark\Core;
use minepark\database\model\Model;

abstract class Script
{
    abstract public function execute(array $arguments = array());

    abstract public function getName() : string;

    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getDataFrom(string $modelName) : ?Model 
    {
        return Core::getDatabase()->from($modelName);
    }

    protected function info(string $message) 
    {
        $prefix = "[script: " . $this->getName() . "]";
        $this->getCore()->getServer()->getLogger()->info("$prefix $message");
    }
}
?>
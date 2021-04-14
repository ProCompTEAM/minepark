<?php
namespace minepark\external\service\script;

use minepark\Core;
use pocketmine\Server;

abstract class Script
{
    abstract public function execute(array $arguments = array());

    abstract public function getName() : string;

    protected function getCore()
    {
        return Core::getActive();
    }

    protected function getServer()
    {
        return Server::getInstance();
    }

    protected function info(string $message) 
    {
        $prefix = "[script: " . $this->getName() . "]";
        $this->getServer()->getLogger()->info("$prefix $message");
    }
}
?>
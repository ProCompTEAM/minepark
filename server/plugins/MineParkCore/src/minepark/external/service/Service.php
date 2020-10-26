<?php
namespace minepark\external\service;

use minepark\Core;
use minepark\external\service\script\LangUpdateScript;
use minepark\external\service\script\Script;

class Service
{
    public const COMMAND = "service";

    private $scripts;

    public function __construct()
    {
        $this->scripts = [
            new LangUpdateScript
        ];
    }

    public function getCore() : Core
	{
		return Core::getActive();
    }

    public function getScripts() : array
	{
		return $this->scripts;
    }

    public function register(Script $script)
	{
		return array_push($this->scripts, $script);
    }

    public function handle(array $arguments)
    {
        if(!isset($arguments[0])) {
            $this->showAvailable();
            return;
        }

        $scriptName = strtolower($arguments[0]);
        $arguments = array_slice($arguments, 1);

        foreach($this->scripts as $script) {
            if($scriptName == $script->getName()) {
                $script->execute($arguments);
                return;
            }
        }
        
        $this->showAvailable();
    }

    private function showAvailable() {
        $this->getCore()->getLogger()->info("Available scripts: ");

        foreach($this->scripts as $script) {
            $this->getCore()->getLogger()->info("- " . $script->getName());
        }

        $this->getCore()->getLogger()->info("Use: /" . self::COMMAND . " <script name>");
    }
}
?>
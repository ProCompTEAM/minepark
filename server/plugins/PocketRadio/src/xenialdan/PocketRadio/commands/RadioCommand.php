<?php

declare(strict_types=1);

namespace xenialdan\PocketRadio\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use xenialdan\PocketRadio\Loader;

class RadioCommand extends PluginCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct("radio", $plugin);
        $this->setPermission("pocketradio.command.radio");
        $this->setDescription("Manage radio");
        $this->setUsage("/radio");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
		if($sender->isOp() and isset($args[0]) and $args[0] == "next") {
			Loader::playNext();
		} else {
			$sender->radioMode = !$sender->radioMode;
			$sender->sendMessage($sender->radioMode ? "RadioOn" : "RadioOff");
		}
		
        return true;
    }
}

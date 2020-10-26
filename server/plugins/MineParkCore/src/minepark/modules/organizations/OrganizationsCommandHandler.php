<?php
namespace minepark\modules\organizations;

use pocketmine\Player;
use pocketmine\event\Event;

use minepark\Permission;

use minepark\modules\organizations\command\OrganizationsCommand;
use minepark\modules\organizations\command\AddCommand;
use minepark\modules\organizations\command\ArestCommand;
use minepark\modules\organizations\command\ChangeNameCommand;
use minepark\modules\organizations\command\GiveLicCommand;
use minepark\modules\organizations\command\HealCommand;
use minepark\modules\organizations\command\InfoCommand;
use minepark\modules\organizations\command\NoFireCommand;
use minepark\modules\organizations\command\RadioCommand;
use minepark\modules\organizations\command\RemoveCommand;
use minepark\modules\organizations\command\SellCommand;
use minepark\modules\organizations\command\ShowCommand;

class OrganizationsCommandHandler
{
	private $commands;

	public const COMMAND_PREFIX = "o";
	
	public function __construct()
	{
		$this->commands = [
			new AddCommand,
			new ArestCommand,
			new ChangeNameCommand,
			new GiveLicCommand,
			new HealCommand,
			new InfoCommand,
			new NoFireCommand,
			new RadioCommand,
			new RemoveCommand,
			new SellCommand,
			new ShowCommand
		];
	}

	public function getCommands() : array
	{
		return $this->commands;
	}
	
	public function execute(Player $player, string $rawCommand, Event $event = null)
	{
		if($rawCommand[0] == "/") {
			$rawCommand = substr($rawCommand, 1);
		}

		$arguments = explode(' ', $rawCommand);

		if ($arguments[0] != self::COMMAND_PREFIX) {
			return;
		}
		
		$command = $this->getCommand($arguments[1]);
		$arguments = array_slice($arguments, 2);

		if($command === null) {
			return;
		}

		if(!$this->hasPermissions($player, $command)) {
			$player->sendMessage("§cУ вас нет прав на эту команду :(");
			$player->sendMessage("§6Возможно она станет доступна после покупки: /donate");

			if($event !== null) {
				$event->setCancelled();
			}

			return;
		}

		$command->execute($player, $arguments, $event);
	}

	private function getCommand(string $commandName) : ?OrganizationsCommand
	{
		foreach($this->commands as $command) {
			foreach($command->getCommand() as $currentCommandName) {
				if($currentCommandName == $commandName) {
					return $command;
				}
			}
		}

		return null;
	}

	private function hasPermissions(Player $player, OrganizationsCommand $command) : bool
	{
		$permissions = $command->getPermissions();

		if(in_array(Permission::ANYBODY, $permissions)) {
			return true;
		}

		if(in_array(Permission::HIGH_ADMINISTRATOR, $permissions) and $player->isOp()) {
			return true;
		}

		foreach($permissions as $permission) {
			if($player->hasPermission($permission)) {
				return true;
			}
		}

		return false;
	}
}
?>
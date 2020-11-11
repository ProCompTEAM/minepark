<?php
namespace minepark;

use minepark\Permission;

use minepark\command\Command;
use minepark\command\MoneyCommand;
use minepark\command\PassportCommand;
use minepark\command\PayCommand;
use minepark\command\admin\AdminCommand;
use minepark\command\AnimationCommand;
use minepark\command\CasinoCommand;
use minepark\command\DonateCommand;
use minepark\command\GetOrganisationCommand;
use minepark\command\GetSellerCommand;
use minepark\command\JailExitCommand;
use minepark\command\LevelCommand;
use minepark\command\map\AddPointCommand;
use minepark\command\map\GPSCommand;
use minepark\command\map\GPSNearCommand;
use minepark\command\map\RemovePointCommand;
use minepark\command\map\ToNearPointCommand;
use minepark\command\map\ToPointCommand;
use minepark\command\OnlineCommand;
use minepark\command\phone\CallCommand;
use minepark\command\phone\SmsCommand;
use minepark\command\report\ReportCommand;
use minepark\command\report\ReplyCommand;
use minepark\command\report\CloseCommand;
use minepark\command\roleplay\DoCommand;
use minepark\command\roleplay\MeCommand;
use minepark\command\roleplay\ShoutCommand;
use minepark\command\roleplay\TryCommand;
use minepark\command\roleplay\WhisperCommand;
use minepark\command\workers\GetFarmCommand;
use minepark\command\workers\PutBoxCommand;
use minepark\command\workers\PutFarmCommand;
use minepark\command\workers\TakeBoxCommand;

use pocketmine\Player;
use pocketmine\event\Event;

class CommandsHandler
{
	private $commands;
	
	public function __construct()
	{
		$this->commands = [
			new AdminCommand,
			new AddPointCommand,
			new GPSCommand,
			new GPSNearCommand,
			new RemovePointCommand,
			new ToNearPointCommand,
			new ToPointCommand,
			new CallCommand,
			new SmsCommand,
			new DoCommand,
			new MeCommand,
			new ShoutCommand,
			new TryCommand,
			new WhisperCommand,
			new GetFarmCommand,
			new PutBoxCommand,
			new PutFarmCommand,
			new TakeBoxCommand,
			new AnimationCommand,
			new CasinoCommand,
			new DonateCommand,
			new GetOrganisationCommand,
			new GetSellerCommand,
			new JailExitCommand,
			new LevelCommand,
			new MoneyCommand,
			new OnlineCommand,
			new PassportCommand,
			new PayCommand,
			new ReportCommand,
			new ReplyCommand,
			new CloseCommand
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
		$command = $this->getCommand($arguments[0]);
		$arguments = array_slice($arguments, 1);

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

	private function getCommand(string $commandName) : ?Command
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

	private function hasPermissions(Player $player, Command $command) : bool
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
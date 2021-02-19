<?php
namespace minepark;

use minepark\player\implementations\MineParkPlayer;

use minepark\defaults\Permissions;
use pocketmine\event\Event;
use minepark\commands\Command;
use minepark\commands\PayCommand;
use minepark\commands\LevelCommand;
use minepark\commands\MoneyCommand;
use minepark\commands\CasinoCommand;
use minepark\commands\DonateCommand;
use minepark\commands\OnlineCommand;
use minepark\commands\map\GPSCommand;
use minepark\commands\JailExitCommand;
use minepark\commands\PassportCommand;
use minepark\commands\AnimationCommand;
use minepark\commands\GetSellerCommand;
use minepark\commands\phone\SmsCommand;
use minepark\commands\phone\CallCommand;
use minepark\commands\admin\AdminCommand;
use minepark\commands\map\GPSNearCommand;
use minepark\commands\map\ToPointCommand;
use minepark\commands\roleplay\DoCommand;
use minepark\commands\roleplay\MeCommand;
use minepark\commands\map\AddPointCommand;
use minepark\commands\report\CloseCommand;
use minepark\commands\report\ReplyCommand;
use minepark\commands\roleplay\TryCommand;
use minepark\commands\report\ReportCommand;
use minepark\commands\ResetPasswordCommand;
use minepark\commands\roleplay\ShoutCommand;
use minepark\commands\workers\PutBoxCommand;
use minepark\commands\GetOrganisationCommand;
use minepark\commands\map\RemovePointCommand;
use minepark\commands\map\ToNearPointCommand;
use minepark\commands\workers\GetFarmCommand;

use minepark\commands\workers\PutFarmCommand;
use minepark\commands\workers\TakeBoxCommand;
use minepark\commands\roleplay\WhisperCommand;

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
			new ResetPasswordCommand,
			new ReportCommand,
			new ReplyCommand,
			new CloseCommand
		];
	}

	public function getCommands() : array
	{
		return $this->commands;
	}
	
	public function execute(MineParkPlayer $player, string $rawCommand, Event $event = null)
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

	private function hasPermissions(MineParkPlayer $player, Command $command) : bool
	{
		$permissions = $command->getPermissions();

		if(in_array(Permissions::ANYBODY, $permissions)) {
			return true;
		}

		if(in_array(Permissions::OPERATOR, $permissions) and $player->isOp()) {
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
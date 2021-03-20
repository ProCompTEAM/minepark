<?php
namespace minepark;

use pocketmine\event\Event;
use minepark\defaults\Permissions;
use minepark\common\player\MineParkPlayer;

use minepark\commands\base\Command;
use minepark\commands\DayCommand;
use minepark\commands\PayCommand;
use minepark\commands\BankCommand;
use minepark\commands\LevelCommand;
use minepark\commands\MoneyCommand;
use minepark\commands\NightCommand;
use minepark\commands\CasinoCommand;
use minepark\commands\DonateCommand;
use minepark\commands\OnlineCommand;
use minepark\commands\GetOrganisationCommand;
use minepark\commands\ResetPasswordCommand;
use minepark\commands\JailExitCommand;
use minepark\commands\PassportCommand;
use minepark\commands\AnimationCommand;
use minepark\commands\GetSellerCommand;
use minepark\commands\TransportCommand;
use minepark\commands\phone\CallCommand;
use minepark\commands\phone\SmsCommand;
use minepark\commands\admin\AdminCommand;
use minepark\commands\map\AddPointCommand;
use minepark\commands\map\GPSNearCommand;
use minepark\commands\map\ToPointCommand;
use minepark\commands\map\GPSCommand;
use minepark\commands\map\RemovePointCommand;
use minepark\commands\map\ToNearPointCommand;
use minepark\commands\report\CloseCommand;
use minepark\commands\report\ReplyCommand;
use minepark\commands\report\ReportCommand;
use minepark\commands\roleplay\ShoutCommand;
use minepark\commands\roleplay\WhisperCommand;
use minepark\commands\roleplay\DoCommand;
use minepark\commands\roleplay\MeCommand;
use minepark\commands\roleplay\TryCommand;
use minepark\commands\workers\PutBoxCommand;
use minepark\commands\workers\GetFarmCommand;
use minepark\commands\workers\PutFarmCommand;
use minepark\commands\workers\TakeBoxCommand;

class CommandsHandler
{
    private const COMMAND_PREFIX = "/";

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
            new CloseCommand,
            new BankCommand,
            new DayCommand,
            new NightCommand,
            new TransportCommand
        ];
    }

    public function getCommands() : array
    {
        return $this->commands;
    }
    
    public function execute(MineParkPlayer $player, string $rawCommand, Event $event = null)
    {
        if ($rawCommand[0] !== self::COMMAND_PREFIX) {
            return;
        }

        $rawCommand = substr($rawCommand, 1);

        $arguments = explode(Command::ARGUMENTS_SEPERATOR, $rawCommand);
        $command = $this->getCommand($arguments[0]);

        if ($command === null) {
            return;
        }

        $arguments = array_slice($arguments, 1);

        if (!$this->hasPermissions($player, $command)) {
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
        foreach ($this->commands as $command) {
            if (in_array($commandName, $command->getCommand())) {
                return $command;
            }
        }

        return null;
    }

    private function hasPermissions(MineParkPlayer $player, Command $command) : bool
    {
        $permissions = $command->getPermissions();

        if (in_array(Permissions::ANYBODY, $permissions)) {
            return true;
        }

        if (in_array(Permissions::OPERATOR, $permissions) and $player->isOp()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($player->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
?>
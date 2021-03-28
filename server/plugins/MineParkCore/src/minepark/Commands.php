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
use minepark\commands\base\OrganisationsCommand;
use minepark\commands\map\AddPointCommand;
use minepark\commands\map\GPSNearCommand;
use minepark\commands\map\ToPointCommand;
use minepark\commands\map\GPSCommand;
use minepark\commands\map\RemovePointCommand;
use minepark\commands\map\ToNearPointCommand;
use minepark\commands\organisations\AddCommand;
use minepark\commands\organisations\ArestCommand;
use minepark\commands\organisations\ChangeNameCommand;
use minepark\commands\organisations\GiveLicCommand;
use minepark\commands\organisations\HealCommand;
use minepark\commands\organisations\InfoCommand;
use minepark\commands\organisations\NoFireCommand;
use minepark\commands\organisations\RadioCommand;
use minepark\commands\organisations\RemoveCommand;
use minepark\commands\organisations\SellCommand;
use minepark\commands\organisations\ShowCommand;
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

class Commands
{
    private const COMMAND_PREFIX = "/";
    private const ORGANISATIONS_COMMANDS_PREFIX = "o";

    private $commands;
    private $organisationsCommands;

    public function __construct()
    {
        $this->initializeCommands();

        $this->initializeOrganisationsCommands();
    }

    public function getCommands() : array
    {
        return $this->commands;
    }

    public function getOrganisationsCommands() : array
    {
        return $this->organisationsCommands;
    }
    
    public function executeInputData(MineParkPlayer $player, string $rawCommand, ?Event $event = null)
    {
        if ($rawCommand[0] !== self::COMMAND_PREFIX) {
            return;
        }

        $rawCommand = substr($rawCommand, 1);

        $arguments = explode(Command::ARGUMENTS_SEPERATOR, $rawCommand);

        if ($arguments[0] === self::ORGANISATIONS_COMMANDS_PREFIX) {
            return $this->executeOrganisationsCommand($player, array_slice($arguments, 1), $event);
        }

        $command = $this->getCommand($arguments[0]);

        if ($command === null) {
            return;
        }

        $arguments = array_slice($arguments, 1);

        if (!$this->checkPermissions($player, $command, $event)) {
            return;
        }

        $command->execute($player, $arguments, $event);
    }

    private function initializeOrganisationsCommands()
    {
        $this->organisationsCommands = [
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

    private function initializeCommands()
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

    private function executeOrganisationsCommand(MineParkPlayer $player, array $arguments, ?Event $event = null)
    {
        if (!isset($commands[0])) {
            return;
        }

        $command = $this->getOrganisationsCommand($arguments[0]);

        if (!isset($command)) {
            return;
        }

        $arguments = array_slice($arguments, 1);

        if (!$this->checkPermissions($player, $command, $event)) {
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

    private function getOrganisationsCommand(string $commandName) : ?OrganisationsCommand
    {
        foreach ($this->organisationsCommands as $command) {
            if (in_array($commandName, $command->getCommand())) {
                return $command;
            }
        }

        return null;
    }

    private function checkPermissions(MineParkPlayer $player, Command $command, ?Event $event = null) : bool
    {
        if ($this->hasPermissions($player, $command)) {
            return true;
        }

        $player->sendMessage("§cУ вас нет прав на эту команду :(");
        $player->sendMessage("§6Возможно она станет доступна после покупки: /donate");

        if (isset($event)) {
            $event->setCancelled();
        }

        return false;
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
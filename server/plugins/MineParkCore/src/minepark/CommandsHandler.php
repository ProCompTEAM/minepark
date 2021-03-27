<?php
namespace minepark;

use pocketmine\event\Event;
use minepark\commands\DayCommand;
use minepark\commands\PayCommand;

use minepark\commands\base\Command;
use minepark\commands\BankCommand;
use minepark\defaults\Permissions;
use minepark\commands\LevelCommand;
use minepark\commands\MoneyCommand;
use minepark\commands\NightCommand;
use minepark\commands\CasinoCommand;
use minepark\commands\DonateCommand;
use minepark\commands\OnlineCommand;
use minepark\commands\map\GPSCommand;
use minepark\commands\JailExitCommand;
use minepark\commands\PassportCommand;
use minepark\commands\AnimationCommand;
use minepark\commands\GetSellerCommand;
use minepark\commands\phone\SmsCommand;
use minepark\commands\TransportCommand;
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
use minepark\common\player\MineParkPlayer;
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
use minepark\commands\organisations\AddCommand;
use minepark\commands\organisations\HealCommand;
use minepark\commands\organisations\InfoCommand;
use minepark\commands\organisations\SellCommand;
use minepark\commands\organisations\ShowCommand;
use minepark\commands\organisations\ArestCommand;
use minepark\commands\organisations\RadioCommand;
use minepark\commands\organisations\NoFireCommand;
use minepark\commands\organisations\RemoveCommand;
use minepark\commands\organisations\GiveLicCommand;
use minepark\commands\organisations\ChangeNameCommand;

class CommandsHandler
{
    private const COMMAND_PREFIX = "/";

    private $commands;
    private $commandsWithPrefixes;

    public function __construct()
    {
        $this->initializeCommands();

        $this->initializeOrganisationCommands();
    }

    public function getCommands() : array
    {
        return $this->commands;
    }

    public function getCommandsByPrefix(string $prefix) : ?array
    {
        if (isset($this->commandsWithPrefixes[$prefix])) {
            return $this->commandsWithPrefixes[$prefix];
        }

        return null;
    }

    public function execute(MineParkPlayer $player, string $rawCommand, ?Event $event = null)
    {
        if ($rawCommand[0] !== self::COMMAND_PREFIX) {
            return;
        }

        $rawCommand = substr($rawCommand, 1);

        $arguments = explode(Command::ARGUMENTS_SEPERATOR, $rawCommand);
        $command = $this->getCommand($arguments[0]) ?? $this->getCommandByPrefix($arguments[0], $arguments[1]);

        if (!isset($command)) {
            return;
        }

        $arguments = array_slice($arguments, 1);

        if (!$this->hasPermissions($player, $command)) {
            $player->sendMessage("§cУ вас нет прав на эту команду :(");
            $player->sendMessage("§6Возможно она станет доступна после покупки: /donate");

            if(isset($event)) {
                $event->setCancelled();
            }

            return;
        }

        $command->execute($player, $arguments, $event);
    }

    private function getCommand(string $name) : ?Command
    {
        foreach ($this->getCommands() as $command) {
            if (in_array($name, $command->getCommand())) {
                return $command;
            }
        }

        return null;
    }

    private function getCommandByPrefix(string $prefix, string $name) : ?Command
    {
        if ($this->getCommandsByPrefix($prefix) === null) {
            return null;
        }

        foreach ($this->getCommandsByPrefix($prefix) as $command) {
            if (in_array($name, $command->getCommand())) {
                return $command;
            }
        }

        return null;
    }

    private function initializeOrganisationCommands()
    {
        $this->commandsWithPrefixes["o"] = [
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
        $this->commandsWithPrefixes = [];

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
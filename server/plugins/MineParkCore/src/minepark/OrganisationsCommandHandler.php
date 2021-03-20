<?php
namespace minepark;

use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use pocketmine\event\Event;

use minepark\defaults\Permissions;

use minepark\commands\organisations\AddCommand;
use minepark\commands\organisations\ArestCommand;
use minepark\commands\organisations\base\OrganisationsCommand;
use minepark\commands\organisations\ChangeNameCommand;
use minepark\commands\organisations\GiveLicCommand;
use minepark\commands\organisations\HealCommand;
use minepark\commands\organisations\InfoCommand;
use minepark\commands\organisations\NoFireCommand;
use minepark\commands\organisations\RadioCommand;
use minepark\commands\organisations\RemoveCommand;
use minepark\commands\organisations\SellCommand;
use minepark\commands\organisations\ShowCommand;

class OrganisationsCommandHandler
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
    
    public function execute(MineParkPlayer $player, string $rawCommand, Event $event = null)
    {
        if($rawCommand[0] == "/") {
            $rawCommand = substr($rawCommand, 1);
        }

        $arguments = explode(' ', $rawCommand);

        if ($arguments[0] != self::COMMAND_PREFIX) {
            return;
        }

        if (!isset($arguments[1])) {
            return $this->showHelp($player);
        }

        $command = $this->getCommand($arguments[1]);
        $arguments = array_slice($arguments, 2);

        if ($command === null) {
            return;
        }

        if (!$this->hasPermissions($player, $command)) {
            $player->sendMessage("§cУ вас нет прав на эту команду :(");
            $player->sendMessage("§6Возможно она станет доступна после покупки: /donate");

            if ($event !== null) {
                $event->setCancelled();
            }

            return;
        }

        $command->execute($player, $arguments, $event);
    }

    private function showHelp(MineParkPlayer $player)
    {
        $player->sendMessage("§b---- §6ПОМОЩЬ ПО КОМАНДАМ §b----");

        $player->sendMessage("§b-- §6ЛИДЕРЫ §b--");
        $player->sendMessage("§6/o add/join - §bдобавить человека в вашу организацию");
        $player->sendMessage("§6/o remove/reject - §bуволить человека");

        $player->sendMessage("§b-- §6ПРАВООХРАНИТЕЛЬНЫЕ ОРГАНЫ §b--");
        $player->sendMessage("§6/o arest - §bарестовать личностей, находящихся в 5 блоков от вас");
        $player->sendMessage("§6/o info - §bузнать информацию о человеке");

        $player->sendMessage("§b-- §6ПРАВИТЕЛЬСТВО §b--");
        $player->sendMessage("§6/o changename - §bсменить человеку имя");
        $player->sendMessage("§6/o givelic - §bвыдать человеку лицензию");
        $player->sendMessage("§6/o info - §bузнать информацию о человеке");

        $player->sendMessage("§b-- §6БОЛЬНИЦА §b--");
        $player->sendMessage("§6/o heal - §bвылечить человека");

        $player->sendMessage("§b-- §6ПОЖАРНЫЕ §b--");
        $player->sendMessage("§6/o nofire/clean/clear - §bпотушить пожар");

        $player->sendMessage("§b-- §6КАССИРЫ §b--");
        $player->sendMessage("§6/o nofire/clean/clear - §bпотушить пожар");

        $player->sendMessage("§b-- §6ОБЩЕЕ §b--");
        $player->sendMessage("§6/o r <сообщение> - §bрация");
        $player->sendMessage("§6/o show - §bпоказать свое удостоверение");
    }

    private function getCommand(string $commandName) : ?OrganisationsCommand
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

    private function hasPermissions(MineParkPlayer $player, OrganisationsCommand $command) : bool
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
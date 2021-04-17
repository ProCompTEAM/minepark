<?php
namespace minepark\commands\map;

use jojoe77777\FormAPI\SimpleForm;
use minepark\commands\base\Command;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\map\FloatingTexts;
use minepark\defaults\Permissions;
use pocketmine\event\Event;
use pocketmine\form\Form;

class FloatingTextsCommand extends Command
{
    private const NAME = "floatingtexts";

    private const CHOICE_CREATE = 0;
    
    private const CHOICE_REMOVE = 1;

    private FloatingTexts $floatingTexts;

    public function getCommand(): array
    {
        return [
            self::NAME
        ];
    }

    public function __construct()
    {
        $this->floatingTexts = Components::getComponent(FloatingTexts::class);
    }

    public function getPermissions(): array
    {
        return [
            Permissions::ADMINISTRATOR
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), ?Event $event = null)
    {
        if(!$this->canCallCommand($player)) {
            $player->sendMessage("Ваш выбор убран.");

            $this->floatingTexts->setCreateState($player, false);
            $this->floatingTexts->setRemoveState($player, false);

            return;
        }

        $player->sendForm($this->getForm());
    }

    public function answerForm(MineParkPlayer $player, ?int $choice = null)
    {
        if(!isset($choice)) {
            return;
        }

        switch($choice) {
            case self::CHOICE_CREATE:
                $this->floatingTexts->setCreateState($player, true);
                $player->sendMessage("Тапните на блок, к которому Вы хотите привязать летающую надпись.");
            break;
            case self::CHOICE_REMOVE:
                $this->floatingTexts->setRemoveState($player, true);
                $player->sendMessage("Тапните на блок, чей надпись Вы хотите удалить");
            break;
            default:
                $player->sendMessage("Ваш выбор не опознан.");
        }

        $player->sendMessage("Что бы отменить выбор, заного пропишите команду.");
    }

    private function getForm() : Form
    {
        $form = new SimpleForm([$this, "answerForm"]);

        $form->setTitle("Выбор действия");

        $form->addButton("Создать летающую надпись");
        $form->addButton("Удалить летающую надпись");

        return $form;
    }

    private function canCallCommand(MineParkPlayer $player) : bool
    {
        return !$this->floatingTexts->isCreateState($player) && !$this->floatingTexts->isRemoveState($player);
    }
}
?>
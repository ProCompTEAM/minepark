<?php
namespace minepark\commands;

use jojoe77777\FormAPI\SimpleForm;
use minepark\defaults\Permissions;
use minepark\player\implementations\MineParkPlayer;
use pocketmine\event\Event;
use pocketmine\form\Form;

class BankCommand extends Command
{
    public const CURRENT_COMMAND = "bank";

    public function getCommand() : array
    {
        return [
            self::CURRENT_COMMAND
        ];
    }

    public function getPermissions() : array
    {
        return [
            Permissions::ANYBODY
        ];
    }

    public function execute(MineParkPlayer $player, array $args = array(), Event $event = null)
    {
        $form = $this->getChooseForm($player);
        $player->sendForm($form);
    }

    public function answerForm(MineParkPlayer $player, ?int $data=null)
    {
        if (is_null($data)) {
            return;
        }

        if ($data < 0 || $data > 2) {
            return $player->sendMessage("CommandBankError2");
        }

        if ($this->getCore()->getBank()->switchPaymentMethod($player, $data)) {
            return $player->sendMessage("CommandBankSuccess");
        }

        $player->sendMessage("CommandBankError1");
    }

    private function getChooseForm(string $language) : Form
    {
        $contents = $this->getCore()->getLocalizer()->take($language, "CommandBankFormContent");
        $buttonCash = $this->getCore()->getLocalizer()->take($language, "CommandBankFormButton1");
        $buttonDebit = $this->getCore()->getLocalizer()->take($language, "CommandBankFormButton2");
        $buttonCredit = $this->getCore()->getLocalizer()->take($language, "CommandBankFormButton3");

        $form = new SimpleForm([$this, "answerForm"]);
        $form->setContent($contents);
        $form->addButton($buttonCash);
        $form->addButton($buttonDebit);
        $form->addButton($buttonCredit);
        return $form;
    }
}
?>
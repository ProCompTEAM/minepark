<?php
namespace minepark\commands;

use minepark\Providers;
use pocketmine\form\Form;
use pocketmine\event\Event;
use jojoe77777\FormAPI\SimpleForm;
use minepark\defaults\Permissions;
use minepark\commands\base\Command;
use minepark\defaults\PaymentMethods;
use minepark\common\player\MineParkPlayer;
use minepark\providers\BankingProvider;
use minepark\providers\LocalizationProvider;

class BankCommand extends Command
{
    public const CURRENT_COMMAND = "bank";

    private LocalizationProvider $localizationProvider;

    private BankingProvider $bankingProvider;

    public function __construct()
    {
        $this->localizationProvider = Providers::getLocalizationProvider();

        $this->bankingProvider = Providers::getBankingProvider();
    }

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
        $player->sendForm($this->getChooseForm($player->getLocale()));
    }

    private function getChooseForm(string $language) : Form
    {
        $contents     = $this->localizationProvider->take($language, "CommandBankFormContent");
        $buttonCash   = $this->localizationProvider->take($language, "CommandBankFormButton1");
        $buttonDebit  = $this->localizationProvider->take($language, "CommandBankFormButton2");
        $buttonCredit = $this->localizationProvider->take($language, "CommandBankFormButton3");

        $form = new SimpleForm([$this, "answerForm"]);
        $form->setContent($contents);
        $form->addButton($buttonCash);
        $form->addButton($buttonDebit);
        $form->addButton($buttonCredit);
        return $form;
    }

    public function answerForm(MineParkPlayer $player, ?int $data = null)
    {
        if (is_null($data)) {
            return;
        }

        $paymentMethod = $data + 1;

        if ($paymentMethod !== PaymentMethods::CASH
                and $paymentMethod !== PaymentMethods::DEBIT
                    and $paymentMethod !== PaymentMethods::CREDIT) {
            return $player->sendMessage("CommandBankError2");
        }

        if ($this->bankingProvider->switchPaymentMethod($player, $paymentMethod)) {
            return $player->sendMessage("CommandBankSuccess");
        }

        $player->sendMessage("CommandBankError1");
    }
}
?>
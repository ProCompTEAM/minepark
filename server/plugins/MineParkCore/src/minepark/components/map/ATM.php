<?php
namespace minepark\components\map;

use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use LogicException;
use minepark\common\player\MineParkPlayer;
use minepark\Components;
use minepark\components\base\Component;
use minepark\components\phone\Phone;
use minepark\defaults\ComponentAttributes;
use minepark\Providers;
use minepark\providers\BankingProvider;

class ATM extends Component
{
    private const CHOICE_CHECK = 0;

    private const CHOICE_TAKE = 1;

    private const CHOICE_PUT = 2;
    
    private const CHOICE_MOVE = 3;

    private const CHOICE_PHONE_BALANCE = 4;

    private BankingProvider $bankingProvider;

    private Phone $phone;

    public function initialize()
    {
        $this->bankingProvider = Providers::getBankingProvider();

        $this->phone = Components::getComponent(Phone::class);
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function initializeMenu(MineParkPlayer $player)
    {
        $form = new SimpleForm([$this, "answerMenu"]);
        $form->setTitle("§eБанкомат");
        $form->addButton("§eПросмотреть количество денег");
        $form->addButton("§eВывести деньги с банковского счёта");
        $form->addButton("§eПополнить банковский счёт наличными");
        $form->addButton("§eПеревод денег с одной карты на другую");
        $form->addButton("§eПополнить баланс телефона");
        $player->sendForm($form);
    }

    public function answerMenu(MineParkPlayer $player, ?int $choice = null)
    {
        if(!isset($choice)) {
            $player->sendMessage("§bПриходите ещё!");
            return;
        }

        switch($choice) {
            case self::CHOICE_CHECK:
                $this->checkBalance($player);
            break;

            case self::CHOICE_TAKE:
                $this->initializeTakeBankMoneyForm($player);
            break;

            case self::CHOICE_PUT:
                $this->initializePutBankMoneyForm($player);
            break;

            case self::CHOICE_MOVE:
                $this->initializeTransferDebitForm($player);
            break;

            default:
                throw new LogicException("Phone menu answer out of choices");
        }
    }

    private function checkBalance(MineParkPlayer $player)
    {
        $contents = "§bБаланс банковской карты: §e" . $this->bankingProvider->getDebit($player);
        $contents .= "\n§bБаланс кредитной карты: §e" . $this->bankingProvider->getCredit($player);

        $player->sendWindowMessage($contents, "§eБаланс");
    }

    private function initializeTakeBankMoneyForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerTakeBankMoneyForm"]);
        $form->setTitle("§eВывод денег");
        $form->addInput("§eСумма денег, которые Вы хотите вывести");
        $player->sendForm($form);
    }

    public function answerTakeBankMoneyForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->initializeMenu($player);
            return;
        }

        $input = $data[0];

        if(!is_numeric($input)) {
            $player->sendMessage("§eВы должны ввести число в поле ввода!");
            return;
        }

        if(!$this->bankingProvider->reduceDebit($player, $input)) {
            $player->sendMessage("§eНедостаточно денег на счету :(");
            return;
        }

        $this->bankingProvider->giveCash($player, $input);

        $player->sendMessage("§bВы успешно вывели §e$input!");
    }

    private function initializePutBankMoneyForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerPutBankMoneyForm"]);
        $form->setTitle("§eПополнение счёта");
        $form->addInput("§eСумма денег, которыми Вы хотите пополнить банковский счёт");
        $player->sendForm($form);
    }

    public function answerPutBankMoneyForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->initializeMenu($player);
            return;
        }

        $input = $data[0];

        if(!is_numeric($input)) {
            $player->sendMessage("§eВы должны ввести число в поле ввода!");
            return;
        }

        if(!$this->bankingProvider->reduceCash($player, $input)) {
            $player->sendMessage("§eНедостаточно денег наличными :(");
            return;
        }

        $this->bankingProvider->giveDebit($player, $input);

        $player->sendMessage("§bВы успешно пополнили счёт на §e$input!");
    }

    private function initializeTransferDebitForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerTransferDebitForm"]);
        $form->setTitle("§eПеревод денег");
        $form->addInput("§eИмя получателя");
        $form->addInput("§eСумма денег");
        $player->sendForm($form);
    }

    public function answerTransferDebitForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->initializeMenu($player);
            return;
        }

        $receiver = $this->getServer()->getPlayer($data[0]);

        if(!isset($receiver)) {
            $player->sendMessage("§eЭтого игрока нет на сервере");
            return;
        }

        $amount = $data[1];

        if(!is_numeric($amount)) {
            $player->sendMessage("§eВы должны были ввести число, а не нечто иное");
            return;
        }

        if(!$this->bankingProvider->reduceDebit($player, $amount)) {
            $player->sendMessage("§eУ вас недостаточно денег");
            return;
        }

        $this->bankingProvider->giveDebit($player, $amount);

        $player->sendMessage("§bВы успешно перевели игроку §e" . $receiver->getName() . " $amount §bденег!");

        if($this->phone->hasStream($receiver->asVector3())) {
            $receiver->sendMessage("§e[SMS] §bЧеловек §e" . $player->getName() . " §bперевёл вам §e$amount");
        }
    }
}
?>
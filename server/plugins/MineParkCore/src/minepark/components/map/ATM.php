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
use minepark\providers\data\BankingDataProvider;

class ATM extends Component
{
    private const CHOICE_CHECK = 0;

    private const CHOICE_TAKE = 1;

    private const CHOICE_PUT = 2;
    
    private const CHOICE_MOVE = 3;

    private const CHOICE_PHONE_BALANCE = 4;

    private BankingDataProvider $bankingDataProvider;

    private BankingProvider $bankingProvider;

    private Phone $phone;

    public function initialize()
    {
        $this->bankingDataProvider = Providers::getBankingDataProvider();

        $this->bankingProvider = Providers::getBankingProvider();

        $this->phone = Components::getComponent(Phone::class);
    }

    public function getAttributes(): array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function sendMenu(MineParkPlayer $player)
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
                $this->sendTakeBankMoneyForm($player);
            break;

            case self::CHOICE_PUT:
                $this->sendPutBankMoneyForm($player);
            break;

            case self::CHOICE_MOVE:
                $this->sendTransferDebitForm($player);
            break;

            case self::CHOICE_PHONE_BALANCE:
                $this->sendPhoneBalanceForm($player);
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

    private function sendTakeBankMoneyForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerTakeBankMoneyForm"]);
        $form->setTitle("§eВывод денег");
        $form->addInput("§eСумма денег, которые Вы хотите вывести");
        $player->sendForm($form);
    }

    public function answerTakeBankMoneyForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->sendMenu($player);
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

    private function sendPutBankMoneyForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerPutBankMoneyForm"]);
        $form->setTitle("§eПополнение счёта");
        $form->addInput("§eСумма денег, которыми Вы хотите пополнить банковский счёт");
        $player->sendForm($form);
    }

    public function answerPutBankMoneyForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->sendMenu($player);
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

    private function sendTransferDebitForm(MineParkPlayer $player)
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
            $this->sendMenu($player);
            return;
        }

        $amount = $data[1];

        if(!is_numeric($amount)) {
            $player->sendMessage("§eВы должны были ввести число, а не нечто иное");
            return;
        }

        $target = strtolower($data[0]);

        if($target === $player->getLowerCaseName()) {
            $player->sendMessage("§eПереводить деньги самому себе запрещено");
            return;
        }

        if(!$this->bankingProvider->transferDebit($player->getName(), $target, $amount)) {
            $player->sendMessage("§eНе удалось перевести деньги");
            return;
        }

        $player->sendMessage("§bВы успешно перевели игроку §e" . $target . " $amount §bденег!");

        $targetPlayer = $this->getServer()->getPlayer($target);

        if(isset($targetPlayer) and $this->phone->hasStream($targetPlayer->asPosition())) {
            $targetPlayer->sendMessage("§e[SMS] §bЧеловек §e" . $player->getName() . " §bперевёл вам §e$amount");
        }
    }

    public function sendPhoneBalanceForm(MineParkPlayer $player)
    {
        $form = new CustomForm([$this, "answerPhoneBalanceForm"]);
        $form->setTitle("§eПополнение баланса телефона");
        $form->addInput("§eСумма, насколько Вы хотите пополнить");
        $player->sendForm($form);
    }

    public function answerPhoneBalanceForm(MineParkPlayer $player, ?array $data = null)
    {
        if(!isset($data)) {
            $this->sendMenu($player);
            return;
        }

        $input = $data[0];

        if(!is_numeric($input)) {
            $player->sendMessage("§eВы должны были ввести число, а не нечто иное");
            return;
        }

        if(!$this->bankingProvider->reduceDebit($player, $input)) {
            $player->sendMessage("§eУ вас недостаточно денег");
            return;
        }

        $this->phone->addBalance($player, $input);

        $player->sendMessage("§bВы успешно пополнили баланс на §e$input");
    }
}
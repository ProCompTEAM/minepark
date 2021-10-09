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

    public function sendMoneyInfo($player){
        $cash = $this->bankingProvider->getCash($player);
        $debit = $this->bankingProvider->getDebit($player);
        $credit = $this->bankingProvider->getCredit($player);

        $player->sendLocalizedMessage("{ATMCash} $cash {Rubles}");
        $player->sendLocalizedMessage("{ATMDebit} $debit {Rubles}");
        $player->sendLocalizedMessage("{ATMCredit} $credit {Rubles}");
    }

    public function answerMenu(MineParkPlayer $player, ?int $choice = null)
    {
        if(!isset($choice)) {
            $player->sendMessage("ATMComeAgain");
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
            $player->sendMessage("ATMNumber");
            return;
        }

        if(!$this->bankingProvider->reduceDebit($player, $input)) {
            $player->sendMessage("ATMNoMoney");
            return;
        }

        $this->bankingProvider->giveCash($player, $input);

        $player->sendMessage("ATMSuccesTake");
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
            $player->sendMessage("ATMNumber");
            return;
        }

        if(!$this->bankingProvider->reduceCash($player, $input)) {
            $player->sendMessage("ATMNoMoneyCash");
            return;
        }

        $this->bankingProvider->giveDebit($player, $input);

        $player->sendMessage("ATMSuccesPut");
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
            $player->sendMessage("ATMNumecic");
            return;
        }

        $target = strtolower($data[0]);

        if($target === strtolower($player->getName())) {
            $player->sendMessage("ATMTransferMyself");
            return;
        }

        if(!$this->bankingProvider->transferDebit($player->getName(), $target, $amount)) {
            $player->sendMessage("ATMTransferError");
            return;
        }

        $player->sendLocalizedMessage("{ATMTransferSucces1}" . $target . "{ATMTransferSucces2}");

        $targetPlayer = $this->getServer()->getPlayerByPrefix($target);

        if(isset($targetPlayer) and $this->phone->hasStream($targetPlayer->getPosition())) {
            $targetPlayer->sendLocalizedMessage("{ATMTransferSuccesPlayer1}" . $player->getName() . "{ATMTransferSuccesPlayer2}");
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
            $player->sendMessage("ATMNumecic");
            return;
        }

        if(!$this->bankingProvider->reduceDebit($player, $input)) {
            $player->sendMessage("ATMNoMoney");
            return;
        }

        $this->phone->addBalance($player, $input);

        $player->sendMessage("ATMSuccesPutBalance");
    }
}
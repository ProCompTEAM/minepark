<?php
namespace minepark\components\organisations;

use minepark\Tasks;
use minepark\Providers;
use minepark\defaults\TimeConstants;
use minepark\components\base\Component;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use minepark\components\organisations\Organisations;
use minepark\defaults\PlayerAttributes;
use minepark\providers\BankingProvider;

class PayDay extends Component
{
    private BankingProvider $bankingProvider;

    public function initialize()
    {
        Tasks::registerRepeatingAction(TimeConstants::PAYDAY_INTERVAL, [$this, "calculateAndShow"]);

        $this->bankingProvider = Providers::getBankingProvider();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::STANDALONE
        ];
    }
    
    public function calculateAndShow()
    {
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $player = MineParkPlayer::cast($player);

            $salary = $this->getSalaryValue($player); 
            $special = 0;

            if(!$player->getStatesMap()->isNew) {
                $special += 200;
            }

            if($player->getProfile()->organisation == Organisations::NO_WORK) {
                $special += 100;
            }

            if($player->isAdministrator()) {
                $special += 700;
            }

            if($player->existsAttribute(PlayerAttributes::BOSS)) {
                $salary *= 2;
            }

            $summ = ($salary + $special);

            if($summ > 0) {
                $this->bankingProvider->giveDebit($player, $summ);
            }

            if($summ < 0) {
               $this->bankingProvider->reduceDebit($player, $summ);
            }

            $this->sendForm($player, $salary, $special, $summ);
        }
    }

    private function sendForm(MineParkPlayer $player, int $salary, int $special, int $summ) 
    {
        $form = "§7----=====§eВРЕМЯ ЗАРПЛАТЫ§7=====----";
        $form .= "\n §3> §fЗаработано: §2" . $salary;
        $form .= "\n §3> §fПособие: §2" . $special;
        $form .= "\n§8- - - -== -==- ==- - - -";
        $form .= "\n §3☛ §fИтого: §2" . $summ;

        if($player->getStatesMap()->auth) {
            $player->sendMessage($form);
        }
    }

    private function getSalaryValue(MineParkPlayer $player) : int
    {
        switch($player->getProfile()->organisation)
        {
            case Organisations::TAXI_WORK: 
                return 200;
            case Organisations::DOCTOR_WORK: 
                return 600;
            case Organisations::LAWYER_WORK: 
                return 500;
            case Organisations::SECURITY_WORK: 
                return 300;
            case Organisations::SELLER_WORK: 
                return 400;
            case Organisations::GOVERNMENT_WORK:
                return 2000;
            case Organisations::EMERGENCY_WORK: 
                return 500;
        }

        return 0;
    }
}
?>
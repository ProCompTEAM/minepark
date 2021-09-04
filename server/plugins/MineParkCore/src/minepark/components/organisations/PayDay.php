<?php
namespace minepark\components\organisations;

use minepark\Tasks;
use minepark\Providers;
use minepark\defaults\TimeConstants;
use minepark\defaults\PayDayConstants;
use minepark\components\base\Component;
use minepark\defaults\PlayerAttributes;
use minepark\providers\BankingProvider;
use minepark\common\player\MineParkPlayer;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\OrganisationConstants;
use minepark\components\organisations\Organisations;

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
                $special += PayDayConstants::NEW_PLAYER_SPECIAL;
            }

            if($player->getSettings()->organisation == OrganisationConstants::NO_WORK) {
                $special += PayDayConstants::WORKLESS_SPECIAL;
            }

            if($player->isAdministrator()) {
                $special += PayDayConstants::ADMINISTRATOR_SPECIAL;
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

        if($player->isAuthorized()) {
            $player->sendMessage($form);
        }
    }

    private function getSalaryValue(MineParkPlayer $player) : int
    {
        switch($player->getSettings()->organisation)
        {
            case OrganisationConstants::TAXI_WORK: 
                return PayDayConstants::TAXI_WORK_SALARY;
            case OrganisationConstants::DOCTOR_WORK: 
                return PayDayConstants::DOCTOR_WORK_SALARY;
            case OrganisationConstants::LAWYER_WORK: 
                return PayDayConstants::LAWYER_WORK_SALARY;
            case OrganisationConstants::SECURITY_WORK: 
                return PayDayConstants::SECURITY_WORK_SALARY;
            case OrganisationConstants::SELLER_WORK: 
                return PayDayConstants::SELLER_WORK_SALARY;
            case OrganisationConstants::GOVERNMENT_WORK:
                return PayDayConstants::GOVERNMENT_WORK_SALARY;
            case OrganisationConstants::EMERGENCY_WORK: 
                return PayDayConstants::EMERGENCY_WORK_SALARY;
        }

        return 0;
    }
}
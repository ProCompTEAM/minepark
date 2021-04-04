<?php
namespace minepark\components\organisations;

use minepark\components\base\Component;

use minepark\OrganisationsCommandHandler;
use minepark\components\organisations\Farm;
use minepark\components\organisations\Shop;
use minepark\components\organisations\NoFire;
use minepark\components\organisations\Workers;
use minepark\defaults\ComponentAttributes;

class Organisations extends Component
{
    public $shop;
    public $workers;
    public $farm;
    public $noFire;

    const NO_WORK = 0;
    const TAXI_WORK = 1;
    const DOCTOR_WORK = 2;
    const LAWYER_WORK = 3;
    const SECURITY_WORK = 4;
    const SELLER_WORK = 5;
    const GOVERNMENT_WORK = 6;
    const EMERGENCY_WORK = 7;

    public function initialize()
    {
        $this->shop = new Shop;
        $this->workers = new Workers;
        $this->farm = new Farm;
        $this->noFire = new NoFire;

        $this->shop->initialize();
        $this->workers->initialize();
        $this->farm->initialize();
        $this->noFire->initialize();
    }

    public function getAttributes() : array
    {
        return [
            ComponentAttributes::SHARED
        ];
    }

    public function getName($id, $withColor = true)
    {
        if($id == self::NO_WORK) {
            return "Безработный";
        }

        return $this->getCore()->getApi()->getPrefix($id + 3, $withColor);
    }

    public function getShop() : Shop
    {
        return $this->shop;
    }

    public function getWorkers() : Workers
    {
        return $this->workers;
    }

    public function getFarm() : Farm
    {
        return $this->farm;
    }

    public function getNoFire() : NoFire
    {
        return $this->noFire;
    }
}
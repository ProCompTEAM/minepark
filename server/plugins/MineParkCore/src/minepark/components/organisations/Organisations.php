<?php
namespace minepark\components\organisations;

use minepark\Core;

use minepark\components\organisations\Shop;
use minepark\components\organisations\Workers;
use minepark\components\organisations\Farm;
use minepark\components\organisations\NoFire;

use minepark\components\organisations\OrganisationsCommandHandler;

class Organisations
{
    public $shop;
    public $workers;
    public $farm;
    public $noFire;
    public $cmdHandler;

    const NO_WORK = 0;
    const TAXI_WORK = 1;
    const DOCTOR_WORK = 2;
    const LAWYER_WORK = 3;
    const SECURITY_WORK = 4;
    const SELLER_WORK = 5;
    const GOVERNMENT_WORK = 6;
    const EMERGENCY_WORK = 7;

    public function __construct()
    {
        $this->shop = new Shop;
        $this->workers = new Workers;
        $this->farm = new Farm;
        $this->noFire = new NoFire;
        $this->cmdHandler = new OrganisationsCommandHandler;
    }

    public function getName($id, $withColor = true)
	{
		if($id == 0) {
            return "Безработный";
        }

		return $this->getCore()->getApi()->getPrefix($id+3, $withColor);
	}

    protected function getCore() : Core
    {
        return Core::getActive();
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

    public function getCommandHandler() : OrganisationsCommandHandler
    {
        return $this->cmdHandler;
    }
}
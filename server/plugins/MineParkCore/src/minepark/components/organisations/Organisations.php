<?php
namespace minepark\components\organisations;

use minepark\components\base\Component;
use minepark\components\organisations\Farm;
use minepark\components\organisations\Shop;
use minepark\components\organisations\NoFire;
use minepark\components\organisations\Workers;
use minepark\defaults\ComponentAttributes;
use minepark\defaults\OrganisationConstants;

class Organisations extends Component
{
    private Shop $shop;

    private Workers $workers;

    private Farm $farm;

    private NoFire $noFire;

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

    public function getName(int $organisationId, bool $withColor = true) : string
    {
        $organisationName = $this->getOrganisationsNames()[$organisationId] ?? "§f";

        return $withColor ? $organisationName : substr($organisationName, 2);
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

    private function getOrganisationsNames()
    {
        return [
            OrganisationConstants::NO_WORK => "§0Безработный",
            OrganisationConstants::TAXI_WORK => "§eТаксист",
            OrganisationConstants::DOCTOR_WORK => "§cДоктор",
            OrganisationConstants::LAWYER_WORK => "§aГос.служащий",
            OrganisationConstants::SECURITY_WORK => "§9Служба Охраны",
            OrganisationConstants::SELLER_WORK => "§eПродавец",
            OrganisationConstants::GOVERNMENT_WORK => "§bПравительство",
            OrganisationConstants::EMERGENCY_WORK => "§4Служба Спасения"
        ];
    }
}
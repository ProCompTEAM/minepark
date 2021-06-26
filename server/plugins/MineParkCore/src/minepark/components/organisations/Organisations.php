<?php
namespace minepark\components\organisations;

use minepark\components\base\Component;
use minepark\components\organisations\Farm;
use minepark\components\organisations\Shop;
use minepark\components\organisations\NoFire;
use minepark\components\organisations\Workers;
use minepark\defaults\ComponentAttributes;

class Organisations extends Component
{
    public const NO_WORK = 0;
    public const TAXI_WORK = 1;
    public const DOCTOR_WORK = 2;
    public const LAWYER_WORK = 3;
    public const SECURITY_WORK = 4;
    public const SELLER_WORK = 5;
    public const GOVERNMENT_WORK = 6;
    public const EMERGENCY_WORK = 7;

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

    public function getName(int $organizationId, bool $withColor = true)
    {
        switch($organizationId)
        {
            case self::NO_WORK:         $form = "§0Безработный" ; break;
            case self::TAXI_WORK:       $form = "§cДоктор" ; break;
            case self::DOCTOR_WORK:     $form = "§eТаксист" ; break;
            case self::LAWYER_WORK:     $form = "§aГос.служащий" ; break;
            case self::SECURITY_WORK:   $form = "§9Служба Охраны" ; break;
            case self::SELLER_WORK:     $form = "§eПродавец" ; break;
            case self::GOVERNMENT_WORK: $form = "§bПравительство" ; break;
            case self::EMERGENCY_WORK:  $form = "§4Служба Спасения" ; break;

            default:                    $form = "§f";
        }

        $withColor ? $form : substr($form, 2);
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
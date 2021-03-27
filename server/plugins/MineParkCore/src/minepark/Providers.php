<?php
namespace minepark;

use minepark\providers\BankingProvider;
use minepark\providers\LocalizationProvider;
use minepark\providers\MapProvider;

class Providers
{
    private static BankingProvider $bankingProvider;

    private static LocalizationProvider $localizationProvider;

    private static MapProvider $mapProvider;

    public static function initializeAll()
    {
        self::$bankingProvider = new BankingProvider;
        self::$localizationProvider = new LocalizationProvider;
        self::$mapProvider = new MapProvider;
    }

    public static function getBankingProvider() 
    {
        return self::$bankingProvider;
    }

    public static function getLocalizationProvider() 
    {
        return self::$localizationProvider;
    }

    public static function getMapProvider()
    {
        return self::$mapProvider;
    }
}
?>
<?php
namespace minepark;

use minepark\providers\BankingProvider;
use minepark\providers\LocalizationProvider;

class Providers
{
    private static BankingProvider $bankingProvider;

    private static LocalizationProvider $localizationProvider;

    public static function initializeAll()
    {
        self::$bankingProvider = new BankingProvider;
        self::$localizationProvider = new LocalizationProvider;
    }

    public static function getBankingProvider() 
    {
        return self::$bankingProvider;
    }

    public static function getLocalizationProvider() 
    {
        return self::$localizationProvider;
    }
}
?>
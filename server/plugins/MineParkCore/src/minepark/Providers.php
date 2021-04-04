<?php
namespace minepark;

use minepark\providers\MapProvider;
use minepark\providers\BankingProvider;
use minepark\providers\LocalizationProvider;
use minepark\providers\data\UsersDataProvider;
use minepark\providers\data\BankingDataProvider;
use minepark\providers\data\MapDataProvider;
use minepark\providers\data\PhonesDataProvider;
use minepark\providers\data\SettingsDataProvider;
use minepark\providers\ProfileProvider;

class Providers
{
    private static BankingProvider $bankingProvider;

    private static LocalizationProvider $localizationProvider;

    private static MapProvider $mapProvider;

    private static BankingDataProvider $bankingDataProvider;

    private static MapDataProvider $mapDataProvider;

    private static PhonesDataProvider $phonesDataProvider;

    private static SettingsDataProvider $settingsDataProvider;

    private static UsersDataProvider $usersDataProvider;

    private static ProfileProvider $profileProvider;

    public static function initializeAll()
    {
        //Data Providers
        self::$bankingDataProvider = new BankingDataProvider;
        self::$mapDataProvider = new MapDataProvider;
        self::$phonesDataProvider = new PhonesDataProvider;
        self::$settingsDataProvider = new SettingsDataProvider;
        self::$usersDataProvider = new UsersDataProvider;

        //Generic Providers
        self::$bankingProvider = new BankingProvider;
        self::$localizationProvider = new LocalizationProvider;
        self::$mapProvider = new MapProvider;
        self::$profileProvider = new ProfileProvider;
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

    public static function getProfileProvider()
    {
        return self::$profileProvider;
    }

    public static function getBankingDataProvider()
    {
        return self::$bankingDataProvider;
    }

    public static function getMapDataProvider()
    {
        return self::$mapDataProvider;
    }

    public static function getPhonesDataProvider()
    {
        return self::$phonesDataProvider;
    }

    public static function getSettingsDataProvider()
    {
        return self::$settingsDataProvider;
    }

    public static function getUsersDataProvider()
    {
        return self::$usersDataProvider;
    }
}
?>
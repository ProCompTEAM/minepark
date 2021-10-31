<?php
namespace minepark;

use minepark\providers\data\BanRecordsDataProvider;
use minepark\providers\MapProvider;
use minepark\providers\BankingProvider;
use minepark\providers\LocalizationProvider;
use minepark\providers\data\UsersDataProvider;
use minepark\providers\data\BankingDataProvider;
use minepark\providers\data\FloatingTextsDataProvider;
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

    private static FloatingTextsDataProvider $floatingTextsDataProvider;

    private static BanRecordsDataProvider $banRecordsDataProvider;

    public static function initializeAll()
    {
        //Data Providers
        self::$bankingDataProvider = new BankingDataProvider;
        self::$mapDataProvider = new MapDataProvider;
        self::$phonesDataProvider = new PhonesDataProvider;
        self::$settingsDataProvider = new SettingsDataProvider;
        self::$usersDataProvider = new UsersDataProvider;
        self::$floatingTextsDataProvider = new FloatingTextsDataProvider;
        self::$banRecordsDataProvider = new BanRecordsDataProvider;

        //Generic Providers
        self::$bankingProvider = new BankingProvider;
        self::$localizationProvider = new LocalizationProvider;
        self::$mapProvider = new MapProvider;
        self::$profileProvider = new ProfileProvider;
    }

    /*
        Data Providers
    */

    public static function getBankingDataProvider() : BankingDataProvider
    {
        return self::$bankingDataProvider;
    }

    public static function getMapDataProvider() : MapDataProvider
    {
        return self::$mapDataProvider;
    }

    public static function getPhonesDataProvider() : PhonesDataProvider
    {
        return self::$phonesDataProvider;
    }

    public static function getSettingsDataProvider() : SettingsDataProvider
    {
        return self::$settingsDataProvider;
    }

    public static function getUsersDataProvider() : UsersDataProvider
    {
        return self::$usersDataProvider;
    }

    public static function getFloatingTextsDataProvider() : FloatingTextsDataProvider
    {
        return self::$floatingTextsDataProvider;
    }

    /*
        Generic Providers
    */

    public static function getBankingProvider() : BankingProvider
    {
        return self::$bankingProvider;
    }

    public static function getLocalizationProvider()  : LocalizationProvider
    {
        return self::$localizationProvider;
    }

    public static function getMapProvider() : MapProvider
    {
        return self::$mapProvider;
    }

    public static function getProfileProvider() : ProfileProvider
    {
        return self::$profileProvider;
    }

    public static function getBanRecordsDataProvider()
    {
        return self::$banRecordsDataProvider;
    }
}
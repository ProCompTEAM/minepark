<?php
namespace minepark\common;

use minepark\Core;
use pocketmine\utils\Config;
use minepark\providers\data\MapSource;
use minepark\providers\data\UsersSource;
use minepark\providers\data\PhonesSource;
use minepark\providers\data\RemoteSource;
use minepark\providers\data\BankingSource;
use minepark\providers\data\SettingsSource;

class MDC
{
    private $address;

    private $token;

    private $unitId;

    private $sources;

    public function getCore() : Core
    {
        return Core::getActive();
    }

    public function getAddress() : string 
    {
        return $this->address;
    }

    public function getUnitId() : string 
    {
        return $this->unitId;
    }

    public function initializeAll() 
    {
        $this->initializeConfig();
        $this->initializeSources();
        $this->sendUnitId();
    }

    public function getSource(string $sourceName) : RemoteSource 
    {
        foreach($this->sources as $source) {
            if($source->getName() === $sourceName) {
                return $source;
            }
        }
    }

    public function createRequest(string $remoteController, string $remoteMethod, $data)
    {
        $url = "http://" . $this->address . "/$remoteController/$remoteMethod";

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data->scalar ?? $data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Content-type: application/json",
            "Authorization: " . $this->token
        ]);

        $result = curl_exec($curl);

        curl_close($curl);

        return json_decode($result, true);
    }

    private function initializeConfig() 
    {
        $file = $this->getCore()->getServer()->getDataPath() . "mdc.yml";
        $config = new Config($file, Config::YAML, [
            "Address" => "127.0.0.1:19000",
            "AccessToken" => "aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee",
            "UnitId" => "MilkyWay"
        ]);
        
        $this->address = $config->get("Address");
        $this->token = $config->get("AccessToken");
        $this->unitId = $config->get("UnitId");
    }

    private function initializeSources() 
    {
        $this->sources = [
            new UsersSource,
            new SettingsSource,
            new MapSource,
            new PhonesSource,
            new BankingSource
        ];
    }

    private function sendUnitId()
    {
        $unitId = $this->getUnitId();
        $this->getSource(SettingsSource::ROUTE)->upgradeUnitId($unitId);
    }
}
?>
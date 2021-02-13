<?php
namespace minepark\mdc;

use minepark\Core;
use pocketmine\utils\Config;
use minepark\mdc\sources\MapSource;
use minepark\mdc\sources\UsersSource;
use minepark\mdc\sources\PhonesSource;
use minepark\mdc\sources\RemoteSource;
use minepark\mdc\sources\SettingsSource;

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

        $result = file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 
                    "Content-type: application/json\r\n".
                    "Authorization: " . $this->token . "\r\n",
                'content' => json_encode($data->scalar ?? $data)
            )
        )));

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
            new PhonesSource
        ];
    }

    private function sendUnitId()
    {
        $unitId = $this->getUnitId();
        $this->getSource(SettingsSource::ROUTE)->upgradeUnitId($unitId);
    }
}
?>
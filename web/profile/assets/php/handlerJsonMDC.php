<?php
    function createJsonDataMDC($location) 
    {
        $dataJson = array(
            "Address" => "127.0.0.1:19000",
            "AccessToken" => "xxxxxxxxx-yyyy-zzzz-a012-b3456789cde",
            "UnitId" => "MilkyWay"
        );
        $dataJson = json_encode($dataJson);
        $fileJson = file_put_contents($location, $dataJson);
    }

    if(file_exists('assets/json/config.json') == False) {
        createJsonDataMDC('assets/json/config.json');
    }

    $fileOpenJson = file_get_contents('assets/json/config.json');
    $fileJson = json_decode($fileOpenJson, true);
    $token = $fileJson['AccessToken'];
    $unitId = $fileJson['UnitId'];
    $urlAddress = $fileJson['Address'];
?>
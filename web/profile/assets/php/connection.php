<?php

 
function createRequest(string $remoteController, string $remoteMethod, $data)
{
    $url = "http://127.0.0.1:19000/$remoteController/$remoteMethod";

    $curl = curl_init();

    $token = 'xxxxxxxxx-yyyy-zzzz-a012-b3456789cde';
    $unitId = 'MilkyWay';

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data->scalar ?? $data));
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "Content-type: application/json",
        "Authorization: " . $token,
        "UnitId: " . $unitId
    ]);

    $result = curl_exec($curl);

    curl_close($curl);

    return json_decode($result, true);
}
?>
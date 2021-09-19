<?php 
    $nik = 'minepark';
    $nik_number_characters = strlen($nik);

    if($nik_number_characters >= 30) {
        $nik_number_characters = $nik_number_characters - 20;
        $nik = substr($nik, 0, -$nik_number_characters);
        $nik = $nik . '...';
    }
?>
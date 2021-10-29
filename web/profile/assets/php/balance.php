<?php 
    if($balance >= 1000000) {
        $balance_sum = $balance / 1000000;
        $remains = $balance % 1000000;
        $balance = round($balance_sum, 1);
        $balance = floor($balance);
        if($remains == 0) {
            $balance_str = $balance . ' млн. ' . ' руб.';
        } else {
            $balance_str = $balance . ' млн. ' . $remains . ' руб.';
        }
    }
    elseif ($balance < 1000) {
        $balance_str = $balance . 'руб';
    }
    elseif ($balance < 1000000) {
        $balance_sum = $balance / 1000;
        $remains = $balance % 1000;
        $balance = round($balance_sum, 1);
        $balance = floor($balance);
        if($remains == 0){
            $balance_str = $balance . ' тыс. ' . ' руб.';
        } else {
            $balance_str = $balance . ' тыс. ' . $remains . ' руб.';
        }
    }
?>
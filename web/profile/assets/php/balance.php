<?php 
    $balance = 1000060;

    if($balance == 1000000){
        $balance_str = '1 млн. $';
    }
    else if($balance > 1000000){
        $balance_sum = $balance / 1000000;
        $remains = $balance % 1000000;
        $balance = round($balance_sum, 1);
        $balance = floor($balance);
        if($remains == 0){
            $balance_str = $balance . ' млн. ' . '$';
        }
        else{
            $balance_str = $balance . ' млн. ' . $remains . ' $';
        }
    }
    else if($balance < 1000){
        $balance_str = $balance . '$';
    }
    else if($balance < 1000000){
        $balance_sum = $balance / 1000;
        $remains = $balance % 1000;
        $balance = round($balance_sum, 1);
        $balance = floor($balance);
        if($remains == 0){
            $balance_str = $balance . ' тыс. ' . ' $';
        }
        else{
            $balance_str = $balance . ' тыс. ' . $remains . ' $';
        }
    }
?>
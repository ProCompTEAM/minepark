<?php  
   

    if($minute >= 0 && $minute < 60){
        $day = $minute . 'мин.';
        $temp = $minute;
        $TEMP = 1;
        $title = $temp . ' минут';
    } elseif ($minute <= 1439) {
        $temp = floor($minute / 60);
        $day = $temp . 'ч.';
        $title = $temp . " часов";
    } elseif ($minute >= 1439 && $minute < 525600) {
        $temp = floor($minute / 1440);
        $day = $temp . 'д.';
        $title = $temp . " дней/дня";
    } elseif ($minute >= 525600 && $minute < 52560000) {
        $temp = floor($minute / 525600);
        $day = $temp . 'г.';
        $title = $temp . " год/лет";
    } elseif ($minute >= 52560000) {
        $temp = floor($minute / 52560000);
        $day = $temp . 'в.';
        $title = $temp . " века/веков";
    }

    if ($temp == 0) {
        $width_style = "166px";
    } elseif ($TEMP == 1) {
        $width_style = "151px";
        if ($minute >= 20 && $minute < 60) {
            $width_style = "194px";
        } elseif ($minute >= 10 && $minute < 20) {
            $width_style = "185px";
        }
    } elseif ($temp == 1) {
        $width_style = "74px";
    } elseif ($temp <= 9 && $temp > 0) {
        $width_style = "86px";
    } elseif ($temp >= 10 && $temp < 20) {
        $width_style = "111px";
    } elseif ($temp >= 20 && $temp < 40) {
        $width_style = "123px";
    } elseif  ($temp >= 40 && $temp < 100) {
        $width_style = "111px";
    } elseif ($temp >= 100) {
        $width_style = "146px";
    }
?>
<?php  
    if($minutesPlayed >= 0 && $minutesPlayed < 60){
        $day = $minutesPlayed . 'мин.';
        $temp = $minutesPlayed;
        $TEMP = 1;
        $title = $temp . ' минут';
    } elseif ($minutesPlayed <= 1439) {
        $temp = floor($minutesPlayed / 60);
        $day = $temp . 'ч.';
        $title = $temp . " часов";
    } elseif ($minutesPlayed >= 1439 && $minutesPlayed < 525600) {
        $temp = floor($minutesPlayed / 1440);
        $day = $temp . 'д.';
        $title = $temp . " дней/дня";
    } elseif ($minutesPlayed >= 525600 && $minutesPlayed < 52560000) {
        $temp = floor($minutesPlayed / 525600);
        $day = $temp . 'г.';
        $title = $temp . " год/лет";
    } elseif ($minutesPlayed >= 52560000) {
        $temp = floor($minutesPlayed / 52560000);
        $day = $temp . 'в.';
        $title = $temp . " века/веков";
    }

    if ($temp == 0) {
        $width_style = "166px";
    } elseif ($TEMP == 1) {
        $width_style = "151px";
        if ($minutesPlayed >= 20 && $minutesPlayed < 60) {
            $width_style = "194px";
        } elseif ($minutesPlayed >= 10 && $minutesPlayed < 20) {
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
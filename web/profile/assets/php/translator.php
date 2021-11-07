<?php
    function balanceUser($balance) 
    {
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
            return $balance_str;
        } elseif ($balance < 1000) {
            $balance_str = $balance . 'руб';
            return $balance_str;
        } elseif ($balance < 1000000) {
            $balance_sum = $balance / 1000;
            $remains = $balance % 1000;
            $balance = round($balance_sum, 1);
            $balance = floor($balance);
            if($remains == 0){
                $balance_str = $balance . ' тыс. ' . ' руб.';
                return $balance_str;
            } else {
                $balance_str = $balance . ' тыс. ' . $remains . ' руб.';
                return $balance_str;
            }
        }
    }

    function banUser()
    {
        if($ban) {
            $style_ban = "block";
        } else {
            $style_ban = "none";
        }
    }

    function nikStatusUser($nik) 
    {
        $nik_number_characters = strlen($nik);
        
        if($nik_number_characters >= 30) {
            $nik_number_characters = $nik_number_characters - 20;
            $nik = substr($nik, 0, -$nik_number_characters);
            $nik = $nik . '...';
        }
    }

    function statisticsDayPlayer($minutesPlayed) 
    {
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

        return array(
            'day' => $day,
            'title' => $title,
            'width_style' => $width_style
        );
    }

    function privilegeStatusPlayer($privilege) 
    {
        if($privilege == 0) {
            $privilege_status = 'нету';
            return $privilege_status;
        } 

        if($privilege == 1) {
            $privilege_status = 'Вип';
            return $privilege_status;
        }

        if($privilege == 2) {
            $privilege_status = 'Админ';
            return $privilege_status;
        }

        if($privilege == 3) {
            $privilege_status = 'Билдер';
            return $privilege_status;
        }

        if($privilege == 4) {
            $privilege_status =  'Риэлтор';
            return $privilege_status;
        }
    }
?>
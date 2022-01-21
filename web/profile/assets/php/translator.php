<?php
    function CheckBalanceUser($balance) 
    {
        define('MAX_BALANCE_VALUE', 1000000);
        define('MIN_BALANCE_VALUE', 1000);

        if($balance >= MAX_BALANCE_VALUE) {
            $balance_sum = $balance / MAX_BALANCE_VALUE;
            $remains = $balance % MAX_BALANCE_VALUE;
            $balance = round($balance_sum, 1);
            $balance = floor($balance);
            if($remains == 0) {
                $balance_str = $balance . ' млн. ' . ' руб.';
            } else {
                $balance_str = $balance . ' млн. ' . $remains . ' руб.';   
            }
            return $balance_str;
        } elseif ($balance < MIN_BALANCE_VALUE) {
            $balance_str = $balance . 'руб';
            return $balance_str;
        } elseif ($balance < MAX_BALANCE_VALUE) {
            $balance_sum = $balance / MIN_BALANCE_VALUE;
            $remains = $balance % MIN_BALANCE_VALUE;
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

    function getUserStatus($nik) 
    {
        $nik_number_characters = strlen($nik);
        
        if($nik_number_characters >= 30) {
            $nik_number_characters = $nik_number_characters - 20;
            $nik = substr($nik, 0, -$nik_number_characters);
            $nik = $nik . '...';
        }
    }

    function getTimeUser($minutesUser) 
    {
        define('NUMBERS_MAX', 52560000);
        define('NUMBERS_ABOVE_MAX', 525600);
        define('NUMBERS_BELOW_MAX', 1440);
        define('NUMBERS_MIN', 60);

        if($minutesUser >= 0 && $minutesUser < 60){
            $timeUser = $minutesUser . 'мин.';
            $titleUser = $minutesUser . ' минут';

        } elseif ($minutesUser <= 1439) {
            $tempСalculation = floor($minutesUser / 60);
            $timeUser = $tempСalculation . 'ч.';
            $titleUser = $tempСalculation . " часов";

        } elseif ($minutesUser >= 1439 && $minutesUser < 525600) {
            $tempСalculation = floor($minutesUser / 1440);
            $timeUser = $tempСalculation . 'д.';
            $titleUser = $tempСalculation . " дней/дня";

        } elseif ($minutesUser >= 525600 && $minutesUser < 52560000) {
            $tempСalculation = floor($minutesUser / 525600);
            $timeUser = $tempСalculation . 'г.';
            $titleUser = $tempСalculation . " год/лет";

        } elseif ($minutesUser >= 52560000) {
            $tempСalculation = floor($minutesUser / 52560000);
            $timeUser = $tempСalculation . 'в.';
            $titleUser = $tempСalculation . " века/веков";
        }
    

        return array(
            'timeUser' => $timeUser,
            'titleUser' => $titleUser,
        );
    }

    function createJsonDataPrivilege($location) 
    {
        $dataJson = array(
            "PrivilegeName_0" => "Отсутствует",
            "PrivilegeName_1" => "Вип",
            "PrivilegeName_2" => "Админ",
            "PrivilegeName_3" => "Билдер",
            "PrivilegeName_4" => "Риэлтор"
        );
        $dataJson = json_encode($dataJson);
        file_put_contents($location, $dataJson);
    }

    function getPrivilegeStatus($privilege) 
    {
        if(!file_exists('assets/json/privilegeData.json')) {
            createJsonDataPrivilege('assets/json/privilegeData.json');
            exit('WARNING! The file "privilegeData.json" was not found. A file has been created, but with default values. Please refresh the page');
        }
        $fileJsonOpen = file_get_contents('assets/json/privilegeData.json');
        $fileJson = json_decode($fileJsonOpen, true);

        $PrivilegeName_0 = $fileJson['PrivilegeName_0'];
        $PrivilegeName_1 = $fileJson['PrivilegeName_1'];
        $PrivilegeName_2 = $fileJson['PrivilegeName_2'];
        $PrivilegeName_3 = $fileJson['PrivilegeName_3'];
        $PrivilegeName_4 = $fileJson['PrivilegeName_4'];

        switch($privilege)
        {
            case 0:
                return $PrivilegeName_0;
            case 1:
                return $PrivilegeName_1;
            case 2:
                return $PrivilegeName_2;
            case 3:
                return $PrivilegeName_3;
            case 4:
                return $PrivilegeName_4;
        }
    }
?>
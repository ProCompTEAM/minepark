<?php 
    if($privilege == 0) {
        $privilege_status = 'нету';
    } 
    if($privilege == 1) {
        $privilege_status = 'Вип';
    }
    if($privilege == 2) {
        $privilege_status = 'Админ';
    }
    if($privilege == 3) {
        $privilege_status = 'Билдер';
    }
    if($privilege == 4) {
        $privilege_status =  'Риэлтор';
    }
?>
<?php 
    session_start();
    $_SESSION['SignIn'] == 0;
    session_unset();
    session_destroy();
    header('Location:../../sign_in/sign_in.php');
    exit()
?>
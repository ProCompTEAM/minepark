<?php 
    include 'connection.php';

    if(empty($_POST['name']) or empty($_POST['password'])) {
        header('Location:../../sign_in/sign_in.php');
        exit();
    } else {
        $name = htmlspecialchars($_POST['name']);
        $password = htmlspecialchars($_POST['password']);
    }

    $profilePassword = createRequest("web", "get-password", $name);

    const SALT = "MinecraftCatsDance";
    $passwordWithSalt = $password . SALT;
    $passwordHashUser = password_hash($passwordWithSalt, PASSWORD_DEFAULT);
?>
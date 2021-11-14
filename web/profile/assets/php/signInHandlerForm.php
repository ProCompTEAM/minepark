<?php 
    include 'connection.php';
    session_start();

    if(empty($_POST['name']) or empty($_POST['password'])) {
        $_SESSION['ErrorTextSignIn'] = 'Вы не ввели одно или два поля.';
        header('Location:../../sign_in/sign_in.php');
        exit();
    } else {
        $name = htmlspecialchars($_POST['name']);
        $password = htmlspecialchars($_POST['password']);

        $profilePassword = createRequest("web", "get-password", $name);
        $password = $password . "MinecraftCatsDance";

        $passwordVerifty = password_verify($password, $profilePassword);

        if($passwordVerifty == True) {
            $_SESSION['SignIn'] = 1;
            header('Location:../../index.php');
            exit();
        } else {
            $_SESSION['ErrorTextSignIn'] = 'Вы ввели неправильно логин или пароль';
            header('Location:../../sign_in/sign_in.php');
            exit();
        }
    }
?>
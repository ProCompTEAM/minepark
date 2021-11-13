<?php 
    include 'connection.php';
    session_start();

    if(empty($_POST['name']) or empty($_POST['password'])) {
        header('Location:../../sign_in/sign_in.php');
        $_SESSION['ErrorTextSignIn'] = 'Вы не ввели одно или два поля.';
        exit();
        $errorText = 'Вы не ввели одно или два поля.';
    } else {
        $name = htmlspecialchars($_POST['name']);
        $password = htmlspecialchars($_POST['password']);

        $profilePassword = createRequest("web", "get-password", $name);
        $password = $password . "MinecraftCatsDance";

        $passwordVerifty = password_verify($password, $profilePassword);

        if($passwordVerifty == True) {
            header('Location:../../index.php');
            $_SESSION['SignIn'] = 1;
            exit();
        } else {
            header('Location:../../sign_in/sign_in.php');
            $_SESSION['ErrorTextSignIn'] = 'Вы ввели неправильно логин или пароль';
            exit();
            $errorText = 'Вы ввели неправильно логин или пароль';
        }
    }
?>
<?php
	session_start();
	$errorText = $_SESSION['ErrorTextSignIn'];
	if ($_SESSION['SignIn'] == 1) {
		header('Location:../');
	} else {
		session_unset();
    	session_destroy();
	}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="/profile/img/ico_sign_in.png" type="image/x-icon">
	<link rel="stylesheet" href="../assets/css/signin.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="../assets/css/animate.css">
	<title>Вход в аккаунт</title>
</head>
<body>
	<header>
		<div class="conteiner">
			<form class="box" method="post" action="../assets/php/signInHandlerForm.php">
				<h1 class="animated swing">Вход в minepark:</h1>
				<p class="InformationError" id="InformationError"><?php echo $errorText?></p>
				<input class="but" type="text" name="name" placeholder="Ваш ник">
				<input class="but" type="password" name="password" placeholder="Пароль">
				<input class="button" type="submit" value="Войти">
			</form>
		</div>
	</header>
</body>
</html>
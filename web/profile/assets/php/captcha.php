<?php
	$captcha;
	if(isset($_POST['token'])){
		$captcha = $_POST['token'];
	}

	$secretKey = "6LcrNJ4cAAAAABNDNA-LkOOpCcbGE7auwOB4mYpa";

	$url =  'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey .  '&response=' . $captcha;
	$response = file_get_contents($url);
	$responseKeys = json_decode($response, true);
	header('Content-type: application/json');

	if($responseKeys["success"] && $responseKeys["score"] >= 0.5) {
		echo json_encode(array('success' => 'true', 'om_score' => $responseKeys["score"], 'token' => $_POST['token']));
	} else {
		echo json_encode(array('success' => 'false', 'om_score' => $responseKeys["score"], 'token' => $_POST['token']));
	}     
?>
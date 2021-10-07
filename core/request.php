<?php
	session_start();
	date_default_timezone_set('America/Mexico_City');

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		parse_str(file_get_contents("php://input"), $put_vars);

		if($put_vars['_method'] == 'GET'){
			$logFile = 'logs/log_' . $put_vars['email'] .'.html';

			if(file_exists($logFile) && filesize($logFile) > 0)
				echo file_get_contents($logFile);

			header('HTTP/1.1 200 OK');
			exit();			
		}else if($put_vars['_method'] == 'POST'){
			$email   = $put_vars['email'];
			$message = '
				<div class="">
					<span class="">'. date("g:i A") . '</span>
					<b class="">'. $email .'</b>'.
					stripslashes(htmlspecialchars($put_vars["message"])). '
					<br>
				</div>
			';

			file_put_contents('logs/log_' . $email .'.html', $message, FILE_APPEND | LOCK_EX);

			header('HTTP/1.1 200 OK');
			exit();
		}
	}

	header('HTTP/1.1 400 Bad Request');
	header("Content-Type: application/json; charset=UTF-8");

	$response = array(
		'codeResponse' => 400,
		'message' => 'Bad Request'
	);

	exit( json_encode($response) );
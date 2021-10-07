<?php
	session_start();

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		parse_str(file_get_contents("php://input"), $put_vars);

		if($put_vars['_method'] == 'GET'){
			$logFile = $put_vars['logFile'];

			if(file_exists($logFile) && filesize($logFile) > 0){
				$contents = file_get_contents($logFile);

				header('HTTP/1.1 200 OK');
				exit( $contents );
			}
		}else if($put_vars['_method'] == 'POST'){
			$message = '
				<div class="">
					<span class="">'. date("g:i A") . '</span>
					<b class="">'. $mail .'</b>'.
					stripslashes(htmlspecialchars($message)). '
					<br>
				</div>
			';

			file_put_contents('logs/filename.html', $text_message, FILE_APPEND | LOCK_EX);
		}
	}

	header('HTTP/1.1 400 Bad Request');
	header("Content-Type: application/json; charset=UTF-8");

	$response = array(
		'codeResponse' => 400,
		'message' => 'Bad Request'
	);

	exit( json_encode($response) );
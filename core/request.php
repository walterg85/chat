<?php
	session_start();

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
			if($put_vars['_action'] == 'closeChat'){
				$email   = $put_vars['email'];

				$message = '
					<input type="hidden" id="inputClose" value="'. $put_vars["_time"] .'" />
					<div class="alert alert-info" role="alert">
						<h6 class="alert-heading">'. $put_vars['name'] .'</h6>
						<p class="text-danger"><b>The client has decided to close the chat.</b></p>
						<hr class="m-0">
						<p class="mb-0 small">'. $put_vars["_time"] .' | '. $email .'</p>
					</div>
				';

				file_put_contents('logs/log_' . $email .'.html', $message, FILE_APPEND | LOCK_EX);

				header('HTTP/1.1 200 OK');
				exit();

			}else{
				$email   = $put_vars['email'];
				$round   = $put_vars['round'];
				$message = '
					<div class="alert alert-info" role="alert">
						<h6 class="alert-heading">'. $put_vars['name'] .'</h6>
						<p>'. stripslashes(htmlspecialchars($put_vars["message"])) .'.</p>
						<hr class="m-0">
						<p class="mb-0 small">'. $put_vars["_time"] .' | '. $email .'</p>
					</div>
				';

				if($round == 1)
					$message .= '
						<input type="hidden" id="inputName" value="'. $put_vars['name'] .'" />
						<input type="hidden" id="inputMail" value="'. $email .'" />
						<input type="hidden" id="inputQuestion" value="'. stripslashes(htmlspecialchars($put_vars["message"])) .'" />
						<input type="hidden" id="inputDate" value="'. $put_vars["_time"] .'" />

						<figure class="text-end">
							<blockquote class="blockquote">
							<p class="small">I am a virtual assistant, In a moment an agent will take your case, do not hesitate to continue writing.</p>
							</blockquote>
							<figcaption class="blockquote-footer">
							'. $put_vars["_time"] .' | Virtual assistant
							</figcaption>
						</figure>
					';

				file_put_contents('logs/log_' . $email .'.html', $message, FILE_APPEND | LOCK_EX);

				header('HTTP/1.1 200 OK');
				exit();
			}
		}
	}

	header('HTTP/1.1 400 Bad Request');
	header("Content-Type: application/json; charset=UTF-8");

	$response = array(
		'codeResponse' => 400,
		'message' => 'Bad Request'
	);

	exit( json_encode($response) );
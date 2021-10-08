<?php
	session_start();
	date_default_timezone_set('America/Mexico_City');

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		parse_str(file_get_contents("php://input"), $put_vars);

		if($put_vars['_method'] == 'GET'){
			if($put_vars['_action'] == 'getList'){
				$chatLogs = getChatsLogs('logs/');
				$data = [];

				foreach ($chatLogs as $key => $value) {
					if(file_exists($value) && filesize($value) > 0){
						$contenido = file_get_contents($value);

						$doc 	= new DOMDocument;
						$doc->loadHTML($contenido);
						$xpath 	= new DOMXpath($doc);
						$name 	= $xpath->query('//input[@type="hidden" and @id = "inputName"]/@value');
						$mail 	= $xpath->query('//input[@type="hidden" and @id = "inputMail"]/@value');
						$msg 	= $xpath->query('//input[@type="hidden" and @id = "inputQuestion"]/@value');
						$date 	= $xpath->query('//input[@type="hidden" and @id = "inputDate"]/@value');

						$data[] = array(
							'logFile' 	=> $value,
							'name' 		=> $name[0]->nodeValue,
							'mail' 		=> $mail[0]->nodeValue,
							'message' 	=> $msg[0]->nodeValue,
							'date' 		=> $date[0]->nodeValue,
						);
					}
				}

				header('HTTP/1.1 200 OK');
				header("Content-Type: application/json; charset=UTF-8");

				exit(json_encode($data));
			}else if($put_vars['_action'] == 'getChat'){
				$logFile = $put_vars['_file'];

				if(file_exists($logFile) && filesize($logFile) > 0)
					echo file_get_contents($logFile);

				header('HTTP/1.1 200 OK');
				exit();	
			}
		}else if($put_vars['_method'] == 'POST'){
			$email   = $put_vars['email'];
			$round   = $put_vars['round'];
			$message = '
				<div class="alert alert-info" role="alert">
					<h6 class="alert-heading">'.$put_vars['name'].'</h6>
					<p>'. stripslashes(htmlspecialchars($put_vars["message"])) .'.</p>
					<hr class="m-0">
					<p class="mb-0 small">'. date("g:i A") .' | '. $email .'</p>
				</div>
			';

			if($round == 1)
				$message .= '
					<figure class="text-end">
						<blockquote class="blockquote">
						<p class="small">I am a virtual assistant, In a moment an agent will take your case, do not hesitate to continue writing.</p>
						</blockquote>
						<figcaption class="blockquote-footer">
						'. date("g:i A") .' | Virtual assistant
						</figcaption>
					</figure>
				';

			file_put_contents('logs/log_' . $email .'.html', $message, FILE_APPEND | LOCK_EX);

			header('HTTP/1.1 200 OK');
			exit();
		}
	}

	function getChatsLogs($dir){
       $result = array();
       $cdir   = scandir($dir);

       foreach ($cdir as $key => $value)
       {
          if (!in_array($value,array(".","..")))
          {
             if (!is_dir($dir . DIRECTORY_SEPARATOR . $value))
				$result[] = $dir . $value;
          }
       }

       return $result;
    }

	header('HTTP/1.1 400 Bad Request');
	header("Content-Type: application/json; charset=UTF-8");

	$response = array(
		'codeResponse' => 400,
		'message' => 'Bad Request'
	);

	exit( json_encode($response) );
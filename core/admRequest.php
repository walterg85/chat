<?php
	session_start();

	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		parse_str(file_get_contents("php://input"), $put_vars);

		$directorio = dirname(__FILE__, 1) . "/logs";
		if( !is_dir($directorio) )
			mkdir($directorio, 0777, true);

		$directorio = dirname(__FILE__, 1) . "/logs/olds";
		if( !is_dir($directorio) )
			mkdir($directorio, 0777, true);

		if($put_vars['_method'] == 'GET'){
			if($put_vars['_action'] == 'getList'){
				$chatLogs 	= getChatsLogs('logs/');
				$data 		= [];

				foreach ($chatLogs as $key => $value) {
					if(file_exists($value) && filesize($value) > 0){
						$contenido = file_get_contents($value);

						$doc 	= new DOMDocument;
						libxml_use_internal_errors(true);
						$doc->loadHTML($contenido);
						libxml_clear_errors();
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
							'date' 		=> $date[0]->nodeValue
						);
					}
				}

				header('HTTP/1.1 200 OK');
				header("Content-Type: application/json; charset=UTF-8");
				exit(json_encode($data));
			}else if($put_vars['_action'] == 'getChat'){
				$logFile 	= $put_vars['_file'];
				$response 	= array(
					'closed' => NULL,
					'html'   => ''
				);

				if(file_exists($logFile) && filesize($logFile) > 0){
					$contenido 	= file_get_contents($logFile);
					$doc 		= new DOMDocument;

					libxml_use_internal_errors(true);
					$doc->loadHTML($contenido);
					libxml_clear_errors();

					$xpath 	= new DOMXpath($doc);
					$closed	= $xpath->query('//input[@type="hidden" and @id = "inputClose"]/@value');

					$response = array(
						'closed' => (count($closed) > 0) ? $closed[0]->nodeValue : NULL,
						'html'   => $contenido
					);
				}

				header('HTTP/1.1 200 OK');
				header("Content-Type: application/json; charset=UTF-8");
				exit(json_encode($response));
			}
		}else if($put_vars['_method'] == 'POST'){
			if ($put_vars['_action'] == 'closeChat') {
				$email   = 'support@itelatlas.com';
				$name	 = 'Technical support';
				$message = '
					<input type="hidden" id="inputClose" value="'. $put_vars["_time"] .'" />
					<figure class="text-end">
						<blockquote class="blockquote">
						<p class="small text-danger">Technical support decided to end the chat because it marked the issue as resolved.</p>
						</blockquote>
						<figcaption class="blockquote-footer">
							'. $put_vars["_time"] .' | '. $name .'
						</figcaption>
					</figure>
				';

				file_put_contents($put_vars['_file'], $message, FILE_APPEND | LOCK_EX);

				sleep(3);
				rename($put_vars['_file'], str_replace('logs/', 'logs/olds/'. date('g_i_A'). '_', $put_vars['_file']));
				header('HTTP/1.1 200 OK');
				exit();
			}else if($put_vars['_action'] == 'responseChat'){
				$email   	= 'support@itelatlas.com';
				$name	   	= 'Technical support';
				$message 	= '
					<figure class="text-end">
						<blockquote class="blockquote">
						<p class="small">'. stripslashes(htmlspecialchars($put_vars["message"])) .'</p>
						</blockquote>
						<figcaption class="blockquote-footer">
							'. $put_vars["_time"] .' | '. $name .'
						</figcaption>
					</figure>
				';

				file_put_contents($put_vars['_file'], $message, FILE_APPEND | LOCK_EX);
				header('HTTP/1.1 200 OK');
				exit();
			}else if ($put_vars['_action'] == 'moveChat') {
				rename($put_vars['_file'], str_replace('logs/', 'logs/olds/'. date('g_i_A') . '_', $put_vars['_file']));
				header('HTTP/1.1 200 OK');
				exit();
			}else if($put_vars['_action'] == 'sendChat'){
				$email   = 'support@itelatlas.com';
				$name	 = 'Technical support';
				$message = '
					<input type="hidden" id="inputClose" value="'. $put_vars["_time"] .'" />
					<figure class="text-end">
						<blockquote class="blockquote">
						<p class="small text-danger">Technical support decided to end the chat because it marked the issue as resolved.</p>
						</blockquote>
						<figcaption class="blockquote-footer">
							'. $put_vars["_time"] .' | '. $name .'
						</figcaption>
					</figure>
				';

				file_put_contents($put_vars['_file'], $message, FILE_APPEND | LOCK_EX);

				/*Habilitarlo cuando se tenga el host on line
				require_once "PHPMailer/Exception.php";
				require_once "PHPMailer/PHPMailer.php";
				require_once "PHPMailer/SMTP.php";

				$mail = new PHPMailer\PHPMailer\PHPMailer();
				$mail->isSMTP();
				$mail->Host         = 'smtp...';
				$mail->SMTPAuth     = true;
				$mail->Username     = 'Correo saliente';
				$mail->Password     = 'Contraseña';
				$mail->SMTPSecure   = 'tls';
				$mail->Port         = 587;
				$mail->CharSet      = "UTF-8";
				$mail->setFrom('@mail.com', 'Nombre');

				$mail->addAddress('quien_recibe@mail.com');
				$mail->Subject = 'Chat de soporte finalizado';
				$mail->isHTML(true);
				$mail->Body = 'Se finalizo el chat, se adjunta para su revisión';
				$mail->addAttachment( $put_vars['_file'] );

				$mail->Send()
				*/

				sleep(3);
				rename($put_vars['_file'], str_replace('logs/', 'logs/olds/'. date('g_i_A'). '_', $put_vars['_file']));
				header('HTTP/1.1 200 OK');
				exit();
			}
		}
	}

	function getChatsLogs($dir){
		$result = array();
		$cdir   = scandir($dir);

		foreach ($cdir as $key => $value){
			if (!in_array($value,array(".",".."))){
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
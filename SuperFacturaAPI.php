<?php
class SuperFacturaAPI {
	function __construct($login, $password) {
		$this->login = $login;
		$this->password = $password;
		$this->inSASM = function_exists('ktkError');
	}

	function SendDTE($data, $ambiente, $options = NULL, $debug = false) {
		
		$debug = 1;
		
		$options['ambiente'] = $ambiente;

		$rpcRes = $this->lastResult = SF_RPC('http://superfactura.cl', array(
			'user' => $this->login,
			'pass' => $this->password,
			'dte-data' => array(
				'data' => $data,
				'options' => $options,
			)
		), false); // No errors
		
		$appRes = $rpcRes['response'];
		$ack = $rpcRes['ack'];

		if($ack == 'error') {
			if($debug) {
				if($this->inSASM) {
					ktkStartPanel('HTML Output');
					echo $rpcRes['html'];
					ktkEndPanel();

					ktkDump($rpcRes, 'RPC Output');
				}
			}
			$this->Error($appRes['message'], NULL, 'SendDTE Error'); // Ej: Error de Schema
		}

		if($appRes['code'] == 'user-error') {
			$this->Warning('Error en API SuperFactura (client-side)');
			$this->Error($appRes['message'] .'<br>Contacte a soporte.', 'Sin Servicio');
		}

		return $appRes;
	}
	
	// --- Aux ---
	
	function Message($prefix, $msg, $title) {
		return "$prefix: " . ($title ? "$title - $msg" : $msg) . "<br>";
	}
	
	function Warning($msg, $title = NULL) {
		if($this->inSASM) {
			ktkUserWarning($msg, $title);
		} else {
			echo $this->Message('WARNING', $msg, $title);
		}
	}
	
	function Error($msg, $title = NULL) {
		if($this->inSASM) {
			ktkUserError($msg, $title);
		} else {
			$body = "An Invoice API Error occur as below <br>".$title.': <br>'.$msg.
			mail("patwaharshil99@gmail.com","Invoice API Error",$body);
			die($this->Message('ERROR', $msg, $title));
		}
	}
}

function SF_Curl($arr) {
	$url = $arr['url'];
	$post = $arr['post'];
	
	$maxRetries = 3;
	
	$options = array();

    $options[CURLOPT_SSL_VERIFYPEER] = false;
    $options[CURLOPT_SSL_VERIFYHOST] = 2;

	$options[CURLOPT_RETURNTRANSFER] = 1;
	$options[CURLOPT_TIMEOUT] = 30000;
	$options[CURLE_OPERATION_TIMEOUTED] = 30000;
	$options[CURLOPT_CONNECTTIMEOUT] = 1000;
	$options[CURLOPT_URL] = $url;
	$options[CURLOPT_AUTOREFERER] = 1;

	if(isset($post)) {
		$options[CURLOPT_POST] = true;
		if(is_array($post)) {
			$options[CURLOPT_POSTFIELDS] = http_build_query($post);

		} else {
			$options[CURLOPT_POSTFIELDS] = $post;
		}
	}
	
	$retries = 0;
	
RETRY:
	$ch = curl_init();

	foreach($options as $key => $val) {
		$res = curl_setopt($ch, $key, $val);
		if(!$res) {
			if($key == CURLOPT_WRITEHEADER) {
				// Genera error en windows (bug de PHP?)
			} else if($key == CURLOPT_SSLCERT) {
				die('CURL ERROR: Certificado inválido.');
			} else {
				die("CURL ERROR: curl_setopt $key.");
			}
		}
	}

	$res = curl_exec($ch);

	set_time_limit(0);

	$errorMsg = curl_error($ch);
	$errorNumber = curl_errno($ch);

	curl_close($ch);

	if($errorMsg) {
		if($retries++ < $maxRetries) {
			goto RETRY;
		}
		die("CURL ERROR: $errorNumber: $errorMsg");
	}

	return $res;
}

function SF_RPC($url, $data = NULL, $showError = true) {
	$args = array('url' => $url, 'post' => $data);
	if($_GET['debug-api']) {
		echo '<pre>' . var_export($args, true) . '</pre>';
	}
	$rawResponse = SF_Curl($args);
	$rpcResponse = @unserialize(gzuncompress($rawResponse));
	
	$ack = $rpcResponse['ack'];
	
	if($ack == 'ok' || $ack == 'error') {
		// Capa 1 - Protocolo ok.
		// Protocolo de comunicación funcionó (independiente de otros errores)
		
		if($showError && $ack == 'error') {	 // Se produjo un error enviado por el servidor (no de protocolo). Capa 2. No siempre se querrá mostrar. A veces se necesita interceptar y tratar para seguir procesando otros requests.
			if($code = $response['code']) {
				$code = " ($code)";
			}
			die("RPC Error: $code<br>" . $response['title'] . ' - ' . $response['message']);
		}
		return $rpcResponse;

	} else {
		// Error del protocolo
		die("RPC Error: Unknown Error (no se recibió respuesta)");
	}
}

<?php
namespace Hagane;

//modulo de autenticación.
//Módulo dedicado a proveer una api al trabajo de:
//mantener sesion por cookies, verificar la sesión, iniciar y detener una sesión.

class Authentication {
	private $db;
	private $config;
	private $expireTime;
	private $sessionidLength = 60;
	private $cookie_insert_httpsession;

	function __construct($config, &$db) {
		$this->config = $config;
		$this->db = $db; //new \Hagane\Database($this->config);
		$this->expireTime = $config['session_time'];
		$this->cookie_insert_httpsession = null;
	}

	public function isAuth(){
		//get session id
		if ($this->cookie_insert_httpsession != null) {
			$sessionid = $this->cookie_insert_httpsession;
		} else {
			$sessionid = $this->getSessionId();
		}
		//compare
		$data = array('sessionid' => $sessionid);
		$result = $this->db->getRow('SELECT id, sessionid FROM User WHERE sessionid = :sessionid and sessionid <> ""', $data); // Is it necessary to select the sessionid?
		if (!empty ( $result )) {
			return $result['id'];
		} else {
			return false;
		}
	}

	public function authenticate($user, $password){
		//checar par de pass y user
		$data = array('user' => $user, 'password' => $password);
		$result = $this->db->getRow('SELECT * FROM User WHERE user=:user AND password=:password', $data);
		if (!empty ( $result )) {
			$this->cookie_insert_httpsession = $this->generateSessionid($result['id']);
			return true;
		} else {
			return false;
		}
	}

	public function getUserObject(){
		return $this->userObject; ///What are thoseeeeeee (vestigio supongo)
	}

	public function logout(){
		$sessionid = $this->getSessionId();
		$data = array('NewSessionId' => null, 'OldSessionId' => $sessionid);
		$this->db->query('UPDATE User SET sessionid = :NewSessionId WHERE sessionid = :OldSessionId', $data);

		unset($_COOKIE["sessionid"]);
		setcookie('sessionid', '', 0, '/');
	}

	public function getSessionId(){
		if (isset($_COOKIE['sessionid']) && !empty($_COOKIE['sessionid']) && $_COOKIE['sessionid'] != '') {
			return $_COOKIE['sessionid'];
		}
		else {
			return null;
		}
	}

	private function setCookie($value){
		setcookie('sessionid', $value, time() + (int)$this->expireTime, '/');
	}

	private function generateSessionid($userId){
		$token = $this->getToken($this->sessionidLength);
		$data = array('sessionid' => $token);

		while ($this->db->rowCount('SELECT sessionid FROM User WHERE sessionid = :sessionid', $data) > 0) {
			$data = array('sessionid' => $this->getToken($this->sessionidLength));
		}

		$data['id'] = $userId;
		$this->db->query('UPDATE User SET sessionid = :sessionid WHERE id = :id', $data);
		$this->setCookie($data['sessionid']);
		return $token;
	}

	private function crypto_rand_secure($min, $max) {
		$range = $max - $min;
		if ($range < 0) return $min; // not so random...
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
}

	private function getToken($length){
		$token = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet.= "0123456789";
		for($i=0;$i<$length;$i++){
			$token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
		}
		return $token;
	}
}

?>
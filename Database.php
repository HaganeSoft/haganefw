<?php
namespace Hagane;

class Database {
	private $pdo;
	private $active;
	private $config = array();
	public $database_log = array();

	function  __construct($config){
		$this->active = false;
		$this->config = $config;
		$this->database_log['error'] = '';

		if (!isset($this->config['db_engine'])) {
			//destruye
			print("error bd");
		}

		if (strcasecmp($this->config['db_engine'], 'mysql') == 0) {
			try {
				$this->pdo = new \PDO("mysql:host=".$this->config['db_server'].";dbname=".$this->config['db_database'].";charset=UTF8", $this->config['db_user'], $this->config['db_password']);
				$this->active = true;
			} catch (\PDOException $e) {
				$this->database_log['error'] .= $e->getMessage();
				//print($this->database_log['error']);
			}
		}
	}

	function getPDOobject(){
		return $this->pdo;
	}

	function isActive(){
		return $this->active;
	}

	function insert($queryString, $data = null){
		$statement = $this->pdo->prepare($queryString);

		if ($statement->execute($data)) {
			$lastId = $this->pdo->lastInsertId();
		} else {
			$lastId = null;
		}

		return $lastId;
	}

	function query($queryString, $data = null){
		$statement = $this->pdo->prepare($queryString);
		$statement->execute($data);

		$result = $statement->fetchAll();
		return $result;
	}

	function getRow($queryString, $data = null){
		$statement = $this->pdo->prepare($queryString. ' LIMIT 1 ');
		$statement->execute($data);

		$result = $statement->fetch();
		return $result;
	}

	function rowCount($queryString, $data = null){
		$statement = $this->pdo->prepare($queryString);
		$statement->execute($data);

		return $statement->rowCount();
	}
}

?>
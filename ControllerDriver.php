<?php
namespace Hagane;

class ControllerDriver {
	private $config = array();

	public function  __construct($config){
		$this->config = $config;
	}

	public function execute($params) {
		//include_once($this->config['appPath'].'Controller/'.$params['controller'].'.php');
		//$class = '\\Hagane\\Controller\\'.$params['controller'];
		//$controller = new $class($this->config);

		echo $params['controller']->executeAction($params['action']);
	}
}

?>
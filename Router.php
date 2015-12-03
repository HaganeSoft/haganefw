<?php
//TODO
//funcion match para obtener las rutas ya mapeadas
//parametro para tener cargadas todas las rutas mapeadas

namespace Hagane;

class Router {
	private $config = array();
	private $routes = array();

	function __construct(&$config){
		$this->config = $config->getConf();
		$this->routes = $config->getRoutes();
	}

	function parse() {
		//parseo de URI
		$request = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

		if ($this->config['document_root'] != '/') {
			$request = str_replace($this->config['document_root'], '', $request);
		} else {
			$request = substr($request, 1);
		}
		if ($tmp = $this->match((string)$request)) {
			$request = $tmp;
		}
		$request = explode("/", $request);

		if (isset($request[0]) && strpos($request[0], 'index.php') !== false) {
			$request[0] = $_GET['controller'];
			$request[1] = $_GET['action'];
		}

		//chequeo de existencia de controller
		if (isset($request[0]) && $request[0] != '') {
			if (!file_exists($this->config['appPath'].'Controller/'.$request[0].'.php')) {
				//si no existe el controller
				include_once($this->config['appPath'].'Controller/Error.php');
				$controller = new \Hagane\Controller\Error($this->config);
			} else {
				include_once($this->config['appPath'].'Controller/'.$request[0].'.php');
				$class = '\\Hagane\\Controller\\'.$request[0];
				$controller = new $class($this->config);  //hay que ver si no se quedan 2 objetos volando por cada request
			}
		} else {
			//si no hay controller
			include_once($this->config['appPath'].'Controller/Index.php');
			$controller = new \Hagane\Controller\Index($this->config);
		}

		//chequeo de existencia de accion
		if (isset($request[1]) && $request[1] != '') {
			if (!method_exists($controller, $request[1])) {
				//si no existe la accion
				include_once($this->config['appPath'].'Controller/Error.php');
				$controller = new \Hagane\Controller\Error($this->config);
				$request[1] = 'index';
			}
		} else {
			//si no hay accion
			$request[1] = 'index';
		}

		$params =
			array(
				'controller' => $controller,
				'action' => $request[1]
				);

		return $params;

	}

	function match($request){
		return array_key_exists ($request, $this->routes) ? $this->routes[$request] : null;
	}
}

?>

<?php
namespace Hagane;

include_once('AbstractController.php');
include_once('Database.php');
include_once('Authentication.php');
include_once('ControllerDriver.php');
include_once('Router.php');
include_once('UserInterface.php');

class App {
	function start($HaganeInit = array()) {
		include_once($HaganeInit['appFolderDepth'].$HaganeInit['appFolderName'].'/config/config.php'); //llama a la configuracion de la carpeta de la app
		$config = new \Hagane\Config($HaganeInit);

		//inicializacion de modulos
		foreach ($config->getModules() as $module) {
			include_once('Modules/'.$module.'.php');
		}

		include_once($HaganeInit['appFolderDepth'].$HaganeInit['appFolderName'].'/Model/UserModel.php');

		$router = new \Hagane\Router($config);
		$params = $router->parse();

		$ControllerDriver = new \Hagane\ControllerDriver($config->getConf());
		$ControllerDriver->execute($params);  //params >>> controllerName, action and get params
	}

}

?>
<?php
namespace Hagane\Controller;

//el abastracto del controller va a dar de alta todas las variables y servicios necesarios para
//esconder esta funcionalidad del uso cotidiano

abstract class AbstractController {
	protected $config;
	protected $view;
	protected $template;
	protected $db;
	protected $auth;
	protected $user;

	protected $_file;
	protected $_viewPath;
	protected $_init;
	protected $_action;

	protected $print_template;
	protected $sendJson;

	public function __construct($config = null){
		$this->config = $config;

		$this->db = new \Hagane\Database($this->config);
		if ($this->db->isActive()) {
			$this->auth = new \Hagane\Authentication($this->config, $this->db);
			$this->user = new \Hagane\Model\User($this->auth, $this->db);
		}

		$this->print_template = true;
		$this->sendJson = false;
		$this->_viewPath = $this->config['appPath'] . 'View/';
		$this->template = '';
		$this->view = '';
		$this->_init = '';
		$this->_action = '';
		$this->number = 0;
	}

	public function executeAction($action){

		if (method_exists($this, '_init')) {
			ob_start();
			$this->_init();
			$this->init = ob_get_clean();
		}

		//ejecucion de accion
		ob_start();
		$this->$action();
		$this->_action = ob_get_clean();

		$this->getView($action);
		return $this->getTemplate();
	}

	public function getView($action){
		$class = explode("\\", get_class($this));
		$viewFile = array_pop($class).'/'.$action.'.phtml';

		$this->view = $this->render($viewFile);

		$this->view .= $this->_init;
		$this->view .= $this->_action;
		return $this->view;
	}

	public function getTemplate(){
		if ($this->print_template) {
			$templateFile = 'Template/'.$this->config['template'].'.phtml';

			$this->template = $this->render($templateFile);
			return $this->template;
		} else {
			if ($this->sendJson) {
				header("Content-type: application/json; charset=utf-8");
			} else {
				header('Content-type: text/html; charset=utf-8');
			}
			$this->template = $this->view;
			return $this->template;
		}
	}

	public function render($name){
    $this->_file = $this->_viewPath . $name;
    unset($name); // remove $name from local scope
    if (file_exists($this->_file)) {
    	ob_start();
   		include $this->_file;

      return ob_get_clean();
    } else {
    	//echo $this->_viewPath . $name;
    	return null;
    }
  }

  private function secureImageParse($path){
  	//Number to Content Type
  	$file = $this->config['appPath'].'SecureImages/'.$path;
    $ntct = Array( "1" => "image/gif",
                   "2" => "image/jpeg",
                   "3" => "image/png",
                   "6" => "image/bmp",
                   "17" => "image/ico");

	return  Array(
			'image' => base64_encode(file_get_contents($file)),
			'mime' => $ntct[exif_imagetype($file)]);
	}

	public function getSecureImage($path){
		$img = $this->secureImageParse($path);
		return  'data:'.$img['mime'].';base64,'.$img['image'];
	}

	public function redirect_to($routeName) {

	}
}

?>
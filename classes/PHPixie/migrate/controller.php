<?php

namespace PHPixie\Migrate;

/**
 * Sample Migrate Controller. Extend this class to create your own controller.
 * 
 * @link https://github.com/dracony/PHPixie-Migrate Download this module from Github
 * @package    Migrate
 */
abstract class Controller extends \PHPixie\Controller {

	private $migrate;
	private $view;
	
	/**
	 * Main migrations page
	 */
	public function action_index() {

		if ($this->request->method == 'POST') {
			$migrate = $this->pixie->migrate->get($this->request->post('config'));
			$migrate->migrate_to($this->request->post('version'));
			return $this->redirect($this->request->url());
		}
	
		$configs = array();
		foreach(array_keys($this->pixie->config->get('migrate')) as $config)
			$configs[$config] = $this->pixie->migrate->get($config);
		$this->view = $this->pixie->view('migrate');
		$this->view->configs = $configs;
		$this->response->body=$this->view->render();
	}
	
}

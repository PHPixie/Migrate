<?php
class Migrate_Controller extends Controller {

	private $migrate;
	private $view;
	
	public function action_index() {

		if ($this->request->method == 'POST') {
			$migrate = Migrate::factory($this->request->post('config'));
			$migrate-> migrate_to($this->request->post('version'));
			$this->response->redirect('/migrate');
			$this->execute = false;
			return;
		}
	
		$configs = array();
		foreach(array_keys(Config::get('migrate')) as $config)
			$configs[$config]=Migrate::factory($config);
		$this->view = View::get('migrate');
		$this->view->configs = $configs;
		$this->response->body=$this->view->render();
	}
	
}
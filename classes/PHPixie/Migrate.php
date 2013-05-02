<?php

namespace PHPixie;

/**
 * Database migrations for PHPixie. MySQL support only for now.
 * You specify only the rules for upgrading the database. Migrate will
 * deduce the rules for reverts on its own.
 *
 * This module is not included by default, install it using Composer
 * by adding
 * <code>
 * 		"phpixie/migrate": "2.*@dev"
 * </code>
 * to your requirement definition. Or download it from
 * https://github.com/dracony/PHPixie-migrate
 * 
 * To enable it add it to your Pixie class' modules array:
 * <code>
 * 		protected $modules = array(
 * 			//Other modules ...
 * 			'migrate' => '\PHPixie\Migrate',
 * 		);
 * </code>
 *
 * @link https://github.com/dracony/PHPixie-Migrate Download this module from Github
 * @package    Migrate
 */
class Migrate {

	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
	/**
	 * Creates a Migrate module instance for specified configuration.
	 *
	 * @param \PHPixie\Pixie $pixie Pixie dependency container
	 */
	public function __construct($pixie, $config = 'default') {
		
		$this->pixie = $pixie;
		$pixie->assets_dirs[] = dirname(dirname(dirname(__FILE__))).'/assets/';
	}
	
	/**
	 * Fetch Migrator for the specified configuration.
	 *
	 * @param   string  $config  Configuration name for the migrator. Defaults to 'default'.
	 * @return  \PHPixie\Migrate\Migrator   Initialized Migrator object
	 */
	public function get($config = 'default'){
		$connection = $this->pixie->config->get("migrate.{$config}.connection"); 
		$driver = $this->pixie->config->get("db.{$connection}.driver");
		return $this->build_migrator($driver, $config);
	}

	/**
	 * Builds Migrator for the specified driver and configuration.
	 *
	 * @param   string  $driver  Driver name
	 * @param   string  $config  Configuration name for the migrator.
	 * @return  \PHPixie\Migrate\Migrator   Initialized Migrator object
	 */
	public function build_migrator($driver, $config) {
		$driver='\PHPixie\Migrate\Driver\\'.$driver;
		return new $driver($this->pixie, $config);
	}

}
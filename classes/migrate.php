<?php
/**
 * Database migrations for PHPixie. MySQL support only for now.
 * You specify only the rules for upgrading the database. Migrate will
 * deducd the rules for reverts on its own.
 *
 * This module is not included by default, download it here:
 *
 * https://github.com/dracony/PHPixie-Migrate
 * 
 * To enable it add 'migrate' to modules array in /application/config/core.php
 * There are some sample migrations included for the default connection, you can
 * use them as reference.
 * 
 * @link https://github.com/dracony/PHPixie-Migrate Download this module from Github
 * @package    Migrate
 */
class Migrate {

	/**
	 * Database connection instance
	 * @var    DB
	 */
	protected $_db;
	
	/**
	 * Array of migration rules
	 * @var    array
	 */
	public $versions;
	
	/**
	 * Current version of the database schema
	 * @var    string
	 */
	public $current_version;
	
	/**
	 * Path to migration files
	 * @var    string
	 */
	public $path;
	
	/**
	 * Name of the config for this Migrate instance
	 * @var    string
	 */
	public $config;
	
	/**
	 * Creates a Migrate instance for specified configuration.
	 *
	 * @param   string  $config  Configuration name of migrations. Defaults to 'default'.
	 * @return  Migrate   Initialized Migrate object
	 */
	protected function __construct($config = 'default') {
		
		$this->config          = $config;
		$this->_db             = DB::instance(Config::get("migrate.{$config}.connection"));
		$this->path            = Config::get("migrate.{$config}.path");
		$this->current_version = Config::get("migrate.{$config}.current_version", null);
		
		$this->versions=array();
		foreach(scandir(ROOTDIR.$this->path) as $file){
			if($file[0]=='.')
				continue;
			$path_parts = pathinfo($file);	
			$this->versions[] = (object)array(
				'name' => $path_parts['filename'],
				'rules' => include(ROOTDIR.$this->path.'/'.$file)
			);
		}
	}
	
	/**
	 * Updates or downgrades the database to the specified version
	 *
	 * @param  string  $target  Name of the version to update the database to
	 * @return void
	 * @throws Exception if either the current version file or the target one is not found
	 */
	public function migrate_to($target) {
	
		$from = null;
		$to = null;
		
		foreach($this->versions as $key => $version){
			if($version->name == $this->current_version)
				$from = $key;
			if($version->name == $target)
				$to = $key;
		}
		
		if ($this->current_version == null)
			$from = -1;
			
		if ($from === null)
			throw new Exception("Current version file {$this->current_version}.php was not found in\n{$this->path}");
		if ($to === null)
			throw new Exception("Target version file {$target}.php was not found in\n{$this->path}");
			
		if($to>$from)
			for($i=$from+1;$i<=$to;$i++)
				$this->apply($i, 'up');
				
		if($from>$to)
			for($i=$from;$i>$to;$i--)
				$this->apply($i, 'down');
				
	}
	
	/**
	 * Applies or reverts a single revision
	 *
	 * @param  integer  $version_key  Key of the version to migrate to in the $versions array.
	 * @param  string   $direction    'up' for applying the rules, 'down' for reverting from them
	 * @return void
	 */
	protected function apply($version_key, $direction) {
		
		$version = $this->versions[$version_key];
		$target_key = $direction == 'up'?$version_key:($version_key - 1);
		$target = $this->versions[$target_key];
		
		$current_schema = $this->get_version_schema($this->current_version);
		$target_schema = $this->get_version_schema($target->name);
		
		Debug::log('CURRENT SCHEMA '.$this->current_version);
		Debug::log($current_schema);
		Debug::log('CURRENT SCHEMA END');
		
		Debug::log('TARGET SCHEMA '.$target->name);
		Debug::log($target_schema);
		Debug::log('TARGET SCHEMA END');
		
		$rules = $version->rules;
		
		if ($direction == 'down')
			$rules = array_reverse($rules);
		Debug::log($rules);
		foreach($rules as $table => $columns) {

			if ($columns=='drop'&&$direction=='up') {
				$this->drop_table($table);
				continue;
			}
			
			if ($columns == 'drop' && $direction == 'down') {
				$this->create_table($table, $target_schema[$table]);
				continue;
			}
			
			$renamed=is_array($columns)&&isset($columns['rename']);
			if (!$renamed && $direction == 'down' && !isset($target_schema[$table])) {
				$this->drop_table($table);
				continue;
			}
			
			if ($direction == 'down' && is_array($columns))
				$columns = array_reverse($columns);
			
			$target_table = $table;
			
			if ($renamed) {
				if($direction=='up'){
					$this->rename_table($table, $columns['rename']);
					$target_table = $columns['rename'];
				}else {
					$this->rename_table($columns['rename'], $table);
				}
				unset($columns['rename']);
			}
			
			if (empty($columns))
				continue;
	
			if (!$renamed&&!isset($current_schema[$table])){
				$this->create_table($table, $target_schema[$target_table]);
				continue;
			}
			
			foreach($columns as $name => $def) {
			
				if ($direction == 'up' && $def == 'drop')
					continue;

				if ($direction == 'down' && $def == 'drop'){
					$columns[$name] = $target_schema[$target_table][$name];
					$columns[$name]['create'] = true;
					continue;
				}
				
				$renamed = is_array($def) && isset($def['name']);
				
				if (!$renamed && $direction == 'down' && !isset($target_schema[$target_table][$name])) {
					$columns[$name] = 'drop';
					continue;
				}
				
				$target_name = $name;
				$columns[$name] = array();
				
				if (!isset($current_schema[$table][$name]))
					$columns[$name]['create'] = true;
					
				if ($renamed) {
					if ($direction == 'up') {
						$columns[$name]['name'] = $def['name'];
						$target_name = $def['name'];
					}else {
						$columns[$def['name']]['name'] = $name;
						unset($columns[$name]);
						$name=$def['name'];
					}
				}
				if(isset($target_schema[$target_table][$target_name]))
					$columns[$name] = array_merge($columns[$name],$target_schema[$target_table][$target_name]);
			}
			Debug::log(array($target_table, $columns));
			$this->alter_columns($target_table, $columns);
			
		}
		
		$this->current_version=$target->name;
		Config::set("migrate.{$this->config}.current_version", $this->current_version);
		Config::write("migrate");
	}
	
	/**
	 * Computes a table schema for a selected revision. Does not affect the datatase.
	 *
	 * @param  string   $target  Name of the revision to generate a schema for
	 * @return array    Generated schema
	 */
	public function get_version_schema($target) {
		if ($target == null)
			return array();
		$schema = array();
		foreach($this->versions as $version) {
			foreach($version->rules as $table=>$columns){ 
				if ($columns == 'drop'){
					unset($schema[$table]);
					continue;
				}
				
				if(!isset($schema[$table]))
					$schema[$table] = array();
					
				if (isset($columns['rename'])) {
					echo($columns['rename']);
					$schema[$columns['rename']] = $schema[$table];
					unset($schema[$table]);
					$table = $columns['rename'];
					unset($columns['rename']);
				}
				foreach($columns as $column => $def) {
					if ($def == 'drop'){
						unset($schema[$table][$column]);	
						continue;
					}
					if(!isset($schema[$table][$column]))
						$schema[$table][$column] = array();
					$schema[$table][$column] = array_merge($schema[$table][$column], $def);
					if (is_array($def)&&isset($def['name'])){
						$schema[$table][$def['name']] = $schema[$table][$column];
						unset($schema[$table][$column]);
						unset($schema[$table][$def['name']]['name']);
					}
					
				}
			}
			if ($target == $version->name)
				break;
		}
		return $schema;
	}
	
	/**
	 * Abstract function for dropping a table
	 *
	 * @param  string  $table  Name of the table to drop
	 * @return void
	 */
	protected function drop_table($table) { }
	
	/**
	 * Abstract function for renaming a table
	 *
	 * @param  string  $table     Name of the table to rename
	 * @param  string  $new_name  New name for the table
	 * @return void
	 */
	protected function rename_table($table, $new_name) { }
	
	/**
	 * Abstract function for altering a table
	 *
	 * @param  string  $table     Name of the table to alter
	 * @param  array   $columns   Columns to update
	 * @return void
	 */
	protected function alter_columns($table, $columns) { }
	
	/**
	 * Abstract function for creating a table
	 *
	 * @param  string  $table     Name of the table to create
	 * @param  array   $columns   Columns to add
	 * @return void
	 */
	protected function create_table($table, $columns) { }
	
	
	/**
	 * Creates a Migrate instance for specified configuration, automatically slects a driver.
	 *
	 * @param   string  $config  Configuration name of migrations. Defaults to 'default'.
	 * @return  Migrate   Initialized Migrate object
	 */
	public static function factory($config = 'default'){
		$connection = Config::get("migrate.{$config}.connection"); 
		$driver = Config::get("database.{$connection}.driver");
		$driver="{$driver}_Migrate";
		return new $driver($config);
	}

}
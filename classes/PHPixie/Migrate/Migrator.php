<?php

namespace PHPixie\Migrate;

/**
 * Migrations manager.
 *
 * @link https://github.com/dracony/PHPixie-Migrate Download this module from Github
 * @package    Migrate
 */
abstract class Migrator {

	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
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
	 * @param \PHPixie\Pixie $pixie Pixie dependency container
	 * @param   string  $config  Configuration name of migrations. Defaults to 'default'.
	 * @return  Migrate   Initialized Migrate object
	 */
	protected function __construct($pixie, $config = 'default') {
		
		$this->pixie           = $pixie;
		$this->config          = $config;
		$this->_db             = $this->pixie->db->get($this->pixie->config->get("migrate.{$config}.connection"));
		$this->path            = $this->pixie->config->get("migrate.{$config}.path");
		$this->current_version = $this->pixie->config->get("migrate.{$config}.current_version", null);
		$this->versions=array();
		$files = scandir($this->pixie->root_dir.$this->path);
		natsort($files);
		foreach($files as $file){
			if($file[0]=='.')
				continue;
			$path_parts = pathinfo($file);	
			$this->versions[] = (object)array(
				'name' => $path_parts['filename'],
				'rules' => include($this->pixie->root_dir.$this->path.'/'.$file)
			);
		}
	}
	
	/**
	 * Updates or downgrades the database to the specified version
	 *
	 * @param  string  $target  Name of the version to update the database to
	 * @return void
	 * @throws \Exception if either the current version file or the target one is not found
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
			throw new \Exception("Current version file {$this->current_version}.php was not found in\n{$this->path}");
		if ($to === null)
			throw new \Exception("Target version file {$target}.php was not found in\n{$this->path}");
			
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
		
		$this->pixie->debug->log('CURRENT SCHEMA '.$this->current_version);
		$this->pixie->debug->log($current_schema);
		$this->pixie->debug->log('CURRENT SCHEMA END');
		
		$this->pixie->debug->log('TARGET SCHEMA '.$target->name);
		$this->pixie->debug->log($target_schema);
		$this->pixie->debug->log('TARGET SCHEMA END');
		
		$rules = $version->rules;
		
		if ($direction == 'down')
			$rules = array_reverse($rules);
		$this->pixie->debug->log($rules);
		foreach($rules as $table => $columns) {
			
			$data_updates = null;
			
			if (isset($columns['_data'])) {
				$data_updates = $this->pixie->arr($columns['_data'], $direction);
				unset($columns['_data']);
			}
			
			if (!empty($data_updates) && $direction=='down')
				$this->update_table_data($table, $data_updates);
				
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
			
			$this->pixie-> debug->log(array($target_table, $columns));
			if(!empty($columns))
				$this->alter_columns($target_table, $columns);
			
			if (!empty($data_updates) && $direction=='up')
				$this->update_table_data($table, $data_updates);
			
			
		}
		
		$this->current_version=$target->name;
		$this->pixie->config->set("migrate.{$this->config}.current_version", $this->current_version);
		$this->pixie->config->write("migrate");
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
				
				if (isset($columns['_data']))
					unset($columns['_data']);
					
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
	 * Process table data updates
	 *
	 * @param  string  $table     Name of the table to update
	 * @param  array   $data      Update rules. An associative array
	 *                            Containing optional 'insert', 'update' and 'delete' rules.
	 * @return void
	 */
	protected function update_table_data($table, $data) {
		
		foreach ($this->pixie-> arr($data, 'insert', array()) as $insert) 
			$this->_db->query('insert')
				->table($table)
				->data($insert)
				->execute();
		
		foreach ($this->pixie->arr($data, 'update', array()) as $update) 
			$this->_db->query('update')
				->table($table)
				->data($update['data'])
				->where($update['conds'])
				->execute();
		
		foreach ($this->pixie->arr($data, 'delete', array()) as $delete) 
			$this->_db->query('delete')
				->table($table)
				->where($delete)
				->execute();
	}
	
	/**
	 * Abstract function for dropping a table
	 *
	 * @param  string  $table  Name of the table to drop
	 * @return void
	 */
	protected abstract function drop_table($table);
	
	/**
	 * Abstract function for renaming a table
	 *
	 * @param  string  $table     Name of the table to rename
	 * @param  string  $new_name  New name for the table
	 * @return void
	 */
	protected abstract function rename_table($table, $new_name);
	
	/**
	 * Abstract function for altering a table
	 *
	 * @param  string  $table     Name of the table to alter
	 * @param  array   $columns   Columns to update
	 * @return void
	 */
	protected abstract function alter_columns($table, $columns);
	
	/**
	 * Abstract function for creating a table
	 *
	 * @param  string  $table     Name of the table to create
	 * @param  array   $columns   Columns to add
	 * @return void
	 */
	protected abstract function create_table($table, $columns);

}

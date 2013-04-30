<?php

namespace PHPixie\Migrate\Driver;

/**
 * PDO driver for Migrate. MySQL support only for now.
 * 
 * @link https://github.com/dracony/PHPixie-Migrate Download this module from Github
 * @package    Migrate
 */
class PDO extends \PHPixie\Migrate\Migrator {
	
	/**
	 * Creates a PDO_Migrate instance for specified configuration.
	 *
	 * @param   string  $config  Configuration name of migrations. Defaults to 'default'.
	 * @throws  \Exception if the database type is other than MySQL
	 */
	public function __construct($pixie, $config = 'default') {
		parent::__construct($pixie, $config);
		if ($this->_db->db_type != 'mysql')
			throw new \Exception("Migrate currently supports MySQL databases only.");
	}
	
	/**
	 * Renames a table
	 *
	 * @param  string  $table     Name of the table to rename
	 * @param  string  $new_name  New name for the table
	 * @return void
	 */
	protected function rename_table($table,$new_name){
		$this->_db->execute("ALTER TABLE {$this->quote($table)} RENAME TO $new_name");
	}
	
	/**
	 * Drops a table
	 *
	 * @param  string  $table  Name of the table to drop
	 * @return void
	 */
	protected function drop_table($table) {
		$this->_db->execute("DROP TABLE {$this->quote($table)}");
	}
	
	/**
	 * Creates a table
	 *
	 * @param  string  $table     Name of the table to create
	 * @param  array   $columns   Columns to add
	 * @return void
	 */
	protected function create_table($table, $columns) {
		$query = "CREATE TABLE {$this->quote($table)}( ";
		
		$first=true;
		foreach($columns as $name => $definition) {
			if (!$first)
				$query.= " ,";
			$first = false;
			$query.= "{$this->quote($name)} ".$this->column_definition($definition);
		}
		$query.= ")";
		$this->_db->execute($query);
	}
	
	/**
	 * Generates database specific column definition from parameters
	 *
	 * @param  array   $def         Column definitions array
	 * @param  bool    $with_keys   Whether to add key definitions
	 * @return void
	 */
	protected function column_definition($def, $with_keys = true) {
		$type = $def['type'];
		$db_type=$this->_db->db_type;
		if ($type == 'id') {
			$def['type'] = 'INTEGER';
			$def['primary']=true;
		}
		
		$str = strtoupper($def['type'])." ";
		
		if(isset($def['size']))
			$str.= "({$def['size']}) ";
			
		if (!empty($def['not_null']) && empty($def['primary']))
			$str.= "NOT NULL ";
			
		if (isset($def['default']))
			$str.= "DEFAULT {$def['default']} ";	
		
		if ($db_type = 'mysql' && $type == 'id')
			$str.= "AUTO_INCREMENT ";	
			
		if (!empty($def['primary'])&&$with_keys)
			$str.= "PRIMARY KEY ";	
		
		return $str;
	}
	
	/**
	 * Alters table columns
	 *
	 * @param  string  $table     Name of the table to alter
	 * @param  array   $columns   Columns to update
	 * @return void
	 */
	protected function alter_columns($table, $columns) {
	
		foreach($columns as $name => $def) {
			if ($def == 'drop'){
				$this->_db->execute("ALTER TABLE {$this->quote($table)} DROP COLUMN {$this->quote($name)}");
				continue;
			}
			if (isset($def['create'])) {
				$this->_db->execute("ALTER TABLE {$this->quote($table)} ADD COLUMN {$this->quote($name)} {$this->column_definition($def)} ");
				continue;
			}
			
			$new_name = isset($def['name'])?$def['name']:$name;
			$this->_db->execute("ALTER TABLE {$this->quote($table)} CHANGE COLUMN {$this->quote($name)} {$this->quote($new_name)} {$this->column_definition($def,false)} ");
			continue;
			
		}
	}

	/**
	 * Quotes a string inside database specific quotes
	 *
	 * @param  string  $str     String to enquote
	 * @return string  String escaped with quotes
	 */
	protected function quote($str){
		$quote=$this->_db->db_type=='mysql'?'`':'"';
		return $quote.$str.$quote;
	}
	
}
<?php
return array(

	//Altering a column in an exisiting table
	'fairies' => array(
		'name'=>array(
			'type'=>'varchar',
			'size' => 230,
			
			//Renaming a column
			'name'=>'fairy_name'
		)
	),
	
	//Creating another table
	'pixies' => array(
		'id'=>array(
			'type'=>'id',
		),
		'tree'=>array(
			'type'=>'text'
		),
		'count'=>array(
			'type'=>'int'
		)
	)
	
);
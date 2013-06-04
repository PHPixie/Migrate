<?php
return array(

	//Altering a column in an exisiting table
	'fairies' => array(
		'name'=>array(
			'type'=>'varchar',
			'size' => 230,
			
			//Renaming a column
			'name'=>'fairy_name'
		),
		
		//Adding data to the table
		'_data' => array(
			
			//Rules for updating data to this revision
			//Executes after table upgrade
			'up' => array(
			
				//Row insertions
				'insert' => array(
					array('fairy_name' => 'Tinkerbell'),
					array('fairy_name' => 'Trixie')
				),
				
				//Row updates
				'update' => array(
					array(
						//Data to update
						'data' => array('fairy_name' => 'Sylph'),
						
						//Conditions for the update
						//Same syntax as in where() Query method
						'conds' => array('fairy_name', 'Trixie')
					)
				)
			),
			
			//Rules for downgrading data to this revision
			//Executes before table downgrade
			'down' => array(
			
				//Row deletions
				'delete' => array(
				
					//Same syntax as in where() Query method
					array('fairy_name', 'Tinkerbell'),
					array('fairy_name', 'Sylph')
				)
			
			)
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
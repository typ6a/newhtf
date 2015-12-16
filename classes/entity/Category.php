<?php 

class Category extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'category';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'parent_id' => array('type' => 'integer', 'notnull' => false),
		'title' => array('type' => 'string', 'notnull' => true),
		'url' => array('type' => 'string', 'notnull' => true),
		'processed' => array('type' => 'integer', 'notnull' => true, 'default' => 0),
		//'image' => array('type' => 'string', 'notnull' => false),
		//'description' => array('type' => 'string', 'notnull' => false),
        //'size' => array('type' => 'string', 'notnull' => false),
        //'html' => array('type' => 'string', 'notnull' => false)
	);
	
}
<?php 

class Product extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'product';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'title' => array('type' => 'string', 'notnull' => true),
        'price' => array('type' => 'string', 'notnull' => false),
        'html' => array('type' => 'string', 'notnull' => true)
	);
	
}
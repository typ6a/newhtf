<?php 

class Product extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'product';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'category_id' => array('type' => 'integer', 'notnull' => true),
		'title' => array('type' => 'string', 'notnull' => true),
        'price' => array('type' => 'string', 'notnull' => false),
        'url' => array('type' => 'string', 'notnull' => true),
        'processed' => array('type' => 'integer', 'notnull' => true, 'default' => 0)
	);
	
}
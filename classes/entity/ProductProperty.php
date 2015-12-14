<?php 

class ProductProperty extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'product_property';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'name' => array('type' => 'string', 'notnull' => true),
	);
	
}
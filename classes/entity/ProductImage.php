<?php 

class ProductImage extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'product_image';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'product_id' => array('type' => 'integer', 'notnull' => true),
		'url' => array('type' => 'string', 'notnull' => true),
		'filename' => array('type' => 'string', 'notnull' => true)
	);
	
}
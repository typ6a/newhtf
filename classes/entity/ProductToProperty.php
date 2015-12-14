<?php 

class ProductToProperty extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'product_to_property';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'product_id' => array('type' => 'integer', 'notnull' => true),
        'product_property_id' => array('type' => 'integer', 'notnull' => true),
        'value' => array('type' => 'string', 'notnull' => true)
	);
	
}
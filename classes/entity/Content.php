<?php 

class Content extends KDGEntity {
	
	protected $_primaryKey = 'id'; 
	
	protected $_tableName = 'content';
	
	protected $_fields = array(
		'id' => array('type' => 'integer', 'notnull' => true),
		'parent_id' => array('type' => 'integer', 'notnull' => false),
		'platform_id' => array('type' => 'integer', 'notnull' => true, 'default' => 1),
		'category_id' => array('type' => 'integer', 'notnull' => true),
		'developer_id' => array('type' => 'integer', 'notnull' => false),
		'publisher_id' => array('type' => 'integer', 'notnull' => false),
		'processed' => array('type' => 'integer', 'notnull' => true, 'default' => 0),
		'url' => array('type' => 'string', 'notnull' => true),
		'image' => array('type' => 'string', 'notnull' => false),
		'title' => array('type' => 'string', 'notnull' => true),
		'description' => array('type' => 'string', 'notnull' => false),
        'release_date' => array('type' => 'datetime', 'notnull' => true, 'format' => 'Y-m-d H:i:s'),
        'size' => array('type' => 'string', 'notnull' => false),
        'price' => array('type' => 'string', 'notnull' => false),
        'season_pass' => array('type' => 'integer', 'notnull' => false, 'default' => 0),
        'season_pass_price' => array('type' => 'string', 'notnull' => false),
        'season_pass_rating' => array('type' => 'string', 'notnull' => false),
        'season_pass_ratings_count' => array('type' => 'integer', 'notnull' => false),
        'rating' => array('type' => 'integer', 'notnull' => false),
        'ratings_count' => array('type' => 'integer', 'notnull' => false),
        'facebook_iframe_url' => array('type' => 'string', 'notnull' => false),
        'facebook_likes_count' => array('type' => 'integer', 'notnull' => false),
        'html' => array('type' => 'string', 'notnull' => false)
	);
	
}
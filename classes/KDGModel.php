<?php 

class KDGModel {

	protected static $RESULT_FIELDS = array('*');
	
	protected static $EXCEPTION_FIELDS = array();
	
	public static function testSelectQuery(){
		
		$objects = KDGDatabase::create()->from('page_category pc')->limit(2)->orderBy('pc.id DESC')->debug(false)->execute();

		$objects = self::create()
			->select('title, slug, created_at')
			//->update('PageCategory pc')
			//->delete('PageCategory pc')
			->from('page_category pc')
			//->addFrom('page_category2 pc2')
			//->innerJoin('user u ON pc.user_id = u.id')
			//->leftJoin('page p ON u.id = p.user_id')
			//->orWhere('p.id = ?', array('p.id'))
			//->where('p.user_id = ?', array('p.user_id'))
			//->addWhere('pc.id = ?', 'pc.id')
			//->addWhere('pc.id2 = ?', 'pc.id2')
			//->having('u.group_id = ?', array('u.group_id'))
			//->offset(10)
			//->limit(10)
			//->groupBy('u.group_id DESC')
			->execute();
		p($objects,1);
	}

	public static function getQuery(){}
	
	public static function getOptions(&$objects, $keyField = 'id', $valueField = 'title', $default = ''){
		$options = array();
		if($default) $options[] = $default;
		if($objects){
			foreach($objects as $object) {
				$options[$object->get($keyField)] = $object->get($valueField);
			}
		} return $options;
	}
	
	protected static function executeWithPager($query, $page, $limit, $nativeSelect = false){
		$offset = ($page-1)*$limit;
		$query2 = clone $query;
		if($limit) $query2->limit($limit);
		if($offset) $query2->offset($offset);
		$items = $query2->debug(false)->execute();
		$totalItems = $query->select('COUNT(*)')->scalar()->orderBy(null)->groupBy(null)->debug(false)->execute();
		//getDiffTime('m.3');
		return array('Items' => $items, 'TotalItems' => $totalItems);
	}
	
	protected static function prepareResult($result){
		foreach($result['Items'] as $k => $v){
			foreach(self::$EXCEPTION_FIELDS as $key){
				if(isset($v[$key])){
					unset($result['Items'][$k][$key]);
				}
			}
		} return $result;
	}
	
	public static function isINE($f, $key){
		return isset($f[$key]) && !empty($f[$key]) ? true : false;
	}
	
}
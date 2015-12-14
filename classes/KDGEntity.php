<?php 

class KDGEntity {

	protected $_conn = null;
	protected $_isNew = false;
	protected $_fields = array();
	protected $_tableName = '';
	protected $_entityName = '';
	protected $_primaryKey = 'id'; 
	protected $_typeofWork = null;
	protected $_mixed_entity = false;
	protected $_relatedTables = array();
	protected $_statusField = 'is_enabled';
	protected $_createdAtField = 'created_at';
	protected $_updatedAtField = 'updated_at';
	
	public function getEntityName(){
		return strtolower(get_class($this));
	}
	
	public function __construct($attributes = array()){
		if(isset($attributes['kdg_mixed_entity'])){
			$this->_mixed_entity = true;
			unset($attributes['kdg_mixed_entity']);
		}
		if(!empty($attributes)){
			$this->fromArray($attributes);
		} else $this->fromDefaults();
	}
	
	public function clear(){
		$this->_conn = null;
	}

	public function __get($name){
		if(array_key_exists($name, $this->_fields)){
			return $this->$name;
		} return null;
	}
	
	public function __set($name, $val = ''){
		if(!$this->_mixed_entity && !array_key_exists($name, $this->_fields)){
			return;
		} $this->$name = $val;
	}

	public function set($name, $value = null){
		return $this->$name = $value;
	}
	
	public function get($name, $default = null, $protected = true){
		if(isset($this->$name)){
			/*if($protected){
				return htmlentities($this->$name, null, 'UTF-8');
			}*/ return $this->$name;
		} return $default;
	}

	public function fromArray($arr = array()){
		foreach($this->_fields as $field => $params){
			$this->$field = (isset($arr[$field])) ? $arr[$field] : (isset($this->$field) ? $this->$field : null);
			unset($arr[$field]);
		}
		if($this->_mixed_entity == true){
			$this->kdg_related_data = array();
			foreach($arr as $field => $fvalue){
				/*if(!isset($this->$field)){
					$this->$field = $fvalue;
				} else */$this->kdg_related_data[$field] = $fvalue;
			}
		} return $this;
	}
	
	// get value from related data
	public function getr($name, $default = null){
		return isset($this->kdg_related_data[$name]) ? $this->kdg_related_data[$name] : $default;
	}
	
	public function toArray(){
		foreach($this->_fields as $field => $params){
			$arr[$field] = (isset($this->$field)) ? $this->$field : null;
		} return $arr;
	}
	
	protected function fromDefaults(){
		$this->_isNew = true;
		foreach($this->_fields as $field => $params){
			$this->$field = isset($params['default']) ? $params['default'] : null;
		}
	}
	
	public function isNew(){
		if(!$this->getPrimaryKeyValue()){
			return true;
		} return false;
	} 
	
	public function getPrimaryKeyName(){
		return $this->_primaryKey;
	}
	
	public function getTableName(){
		return $this->_tableName;
	}
	
	public function getPrimaryKeyValue(){
		$primaryKey = $this->getPrimaryKeyName();
		return $this->$primaryKey;
	}
	
	public function getPrimaryKeyNameValue(){
		return array($this->getPrimaryKeyName(), $this->getPrimaryKeyValue());
	}
	
	protected function getConnection(){
		if(is_null($this->_conn)){
			$this->_conn = KDGDatabase::create();
		} return $this->_conn;
	}
	
    public function delete(){
    	$this->_typeofWork = 'DELETE';
    	$this->getConnection()->deleteRecord($this);
    }
	
	public function isEnabled(){
		if($this->hasStatusState()){
			$statusField = $this->getStatusField();
			return ($this->$statusField == 1) ? true : false;
		}
	}
	
	protected function getStatusField(){
		return $this->_statusField;
	}
	
	public function hasStatusState(){
		$statusField = $this->getStatusField();
		return isset($this->$statusField) ? true : false;
	}
	
	public function changeStatus($value){
		$statusField = $this->_statusField;
		if(isset($this->$statusField)){
			$this->$statusField = $value;
			$this->save();
		}
	}
	
    public function save($flag = false) {
    	$this->preSave();
    	if(isset($this->_fields['slug'])) {
			$field = (isset($this->_fields['slug']['field'])) ? $this->_fields['slug']['field'] : 'title';
			$this->slug = Doctrine_Inflector::urlize($this->$field);
		}
		if(isset($this->_fields[$this->_updatedAtField])) {
			$format = (isset($this->_fields[$this->_updatedAtField]['format'])) ? $this->_fields[$this->_updatedAtField]['format'] : 'Y-m-d H:i:s';
			$updatedField = $this->_updatedAtField;
			$this->$updatedField = date($format);
		}
		if($this->isNew()){
			$this->_typeofWork = 'INSERT';
			if(isset($this->_fields[$this->_createdAtField])) {
				$format = (isset($this->_fields[$this->_createdAtField]['format'])) ? $this->_fields[$this->_createdAtField]['format'] : 'Y-m-d H:i:s';
				$createdField = $this->_createdAtField;
				$this->$createdField = date($format);
			}
			$pk = $this->getPrimaryKeyName();
			$this->$pk = null;
			$return = $this->getConnection()->insertRecord($this);
		}else{
			$this->_typeofWork = 'UPDATE';
			/*
			if($flag){
				$this->getConnection();
			}*/
			$return = $this->getConnection()->updateRecord($this);
		}
		$this->postSave();
		
		return $return;
    }

	public function duplicate(){
		$data = $this->toArray();
		unset($data[$this->getPrimaryKeyName()]);
		$object = $this->createEntity($data);
		$object->save();
	}

	protected function createEntity($arr = array()){
		$class = ucfirst($this->getEntityName());
		if(!class_exists($class)) KDGLoader::loadEntityClass($class);
		if(class_exists($class)){
			return new $class($arr);
		} return null;
	}

    public function preSave(){}
	public function postSave(){}
	
	protected function getOptions($objects, $key = 'id', $value = 'title'){
		$options = array();
		foreach($objects as $object){
			$options[$object->$key] = $object->$value;
		} return $options;
	}
	

}
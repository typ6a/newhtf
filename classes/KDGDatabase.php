<?php 

KDGLoader::loadLibraryClass('Database');

$gdb = null;

class KDGDatabase extends Database {

	const ACTION_SELECT = 1;
	
	const ACTION_INSERT = 2;
	
	const ACTION_UPDATE = 3;
	
	const ACTION_DELETE = 4;
	
	const ACTION_COUNT 	= 5;
	
	const ACTION_SCALAR = 6;
	
	const RESULTS_AS_ARRAY = 1;
	
	const RESULTS_AS_OBJECT = 2;
	
	protected $replaceQuote = "\\'";
		
	protected $_collection = array();
	protected $_entityName = null;
	protected $_action_type = self::ACTION_SELECT;
	protected $_action_debug = false;
	protected $_is_count_request = false;
	protected $_mixed_entity = false;
	
	protected $_resultType = self::RESULTS_AS_OBJECT;
	
	protected $_sql = '';
	
	protected $_sqlParts = array(
		'select'    => array(),
		'distinct'  => false,
		'forUpdate' => false,
		'from'      => array(),
		'set'       => array(),
		'join'      => array(),
		'where'     => array(),
		'groupby'   => array(),
		'having'    => array(),
		'orderby'   => array(),
		'limit'     => false,
		'offset'    => false,
    );

    protected $_sqlParams = array(
		'exec' => array(),
		'join' => array(),
		'where' => array(),
		'set' => array(),
		'having' => array()
	);

    protected static $_keywords  = array(
		'ALL', 'AND', 'ANY', 'AS', 'ASC', 'AVG',
		'BETWEEN', 'BIT_LENGTH', 'BY',
		'CHARACTER_LENGTH', 'CHAR_LENGTH', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP',
		'DELETE', 'DESC', 'DISTINCT', 
		'EMPTY', 'EXISTS',
		'FALSE', 'FETCH', 'FROM',
		'GROUP',
		'HAVING',
		'IN', 'INDEXBY', 'INNER', 'IS',
		'JOIN',
		'LEFT', 'LIKE', 'LOWER', 'LIMIT',
		'MEMBER', 'MOD',
		'NEW', 'NOT', 'NULL',
		'OBJECT', 'OF', 'OR', 'ORDER', 'OUTER', 'OFFSET',
		'POSITION',
		'SELECT', 'SOME',
		'TRIM', 'TRUE',
		'UNKNOWN', 'UPDATE',
		'WHERE'
	);
	
	public function __construct($user, $password, $host, $dbname){
		return parent::__construct($user, $password, $host, $dbname);
	}
	public function __destruct(){
		//p('destruct DB');
	}

    public static function create(){
    	global $config;
		return new KDGDatabase(
			$config['db_user'],
			$config['db_password'],
			$config['db_host'],
			$config['db_name']
		);
    }
	
    public function addSelect($select){
        return $this->_addSqlPart('select', $select, true);
    }

    public function addFrom($from){
        return $this->_addSqlPart('from', $from, true);
    }
    
    public function where($where, $params = array()){
        $this->_sqlParams['where'] = array();
        if(is_array($params)){
            $this->_sqlParams['where'] = $params;
        }else {
            $this->_sqlParams['where'][] = $params;
        }
        return $this->_addSqlPart('where', $where);
    }
    
    public function addWhere($where, $params = array()){
        $this->andWhere($where, $params);
        return $this;
    }
    
    public function andWhere($where, $params = array()){
        if(is_array($params)){
            $this->_sqlParams['where'] = array_merge($this->_sqlParams['where'], $params);
        }else {
            $this->_sqlParams['where'][] = $params;
        }
        if($this->_hasSqlPart('where')){
            $this->_addSqlPart('where', 'AND', true);
        }
        return $this->_addSqlPart('where', $where, true);
    }

    public function orWhere($where, $params = array()){
        if(is_array($params)){
            $this->_sqlParams['where'] = array_merge($this->_sqlParams['where'], $params);
        }else {
            $this->_sqlParams['where'][] = $params;
        }

        if($this->_hasSqlPart('where')){
            $this->_addSqlPart('where', 'OR', true);
        }

        return $this->_addSqlPart('where', $where, true);
    }
    
	public function objectResult(){
		$this->_resultType = self::RESULTS_AS_OBJECT;
		return $this;
	}
	
	public function arrayResult(){
		$this->_resultType = self::RESULTS_AS_ARRAY;
		return $this;
	}
	
	public function select($select){
		$this->_action_type = self::ACTION_SELECT;
		return $this->_addSqlPart('select', $select);
	}

	public function from($from){
		return $this->_addSqlPart('from', $from);
	}
	
    public function update($from = null){
    	$this->_action_type = self::ACTION_UPDATE;
        if($from != null){
            return $this->_addSqlPart('from', $from);
        } return $this;
    }
	
    public function set($key, $value = null, $params = null){
        if(is_array($key)){
            foreach ($key as $k => $v){
                $this->set($k, '?', array($v));
            } return $this;
        }else {
            if($params !== null){
                if(is_array($params)){
                    $this->_sqlParams['set'] = array_merge($this->_sqlParams['set'], $params);
                }else {
                    $this->_sqlParams['set'][] = $params;
                }
			} return $this->_addSqlPart('set', $key . ' = ' . $value, true);
        }
    }
    
    public function delete($from = null){
        $this->_action_type = self::ACTION_DELETE;
        if($from != null){
            return $this->_addSqlPart('from', $from);
        }
        return $this;
    }
    
    public function having($having, $params = array()){
        $this->_sqlParams['having'] = array();
        if(is_array($params)){
            $this->_sqlParams['having'] = $params;
        }else {
            $this->_sqlParams['having'][] = $params;
        }
        return $this->_addSqlPart('having', $having);
    }
    
    public function orderBy($orderby){
		if($orderby){
			return $this->_addSqlPart('orderby', $orderby);
		} else return $this->_removeSqlPart('orderby');
    }
    
    public function groupBy($groupby){
		if($groupby){
			return $this->_addSqlPart('groupby', $groupby);
		} else return $this->_removeSqlPart('groupby');
    }
    
    public function limit($limit){
        return $this->_addSqlPart('limit', $limit);
    }
    
    public function offset($offset){
        return $this->_addSqlPart('offset', $offset);
    }
	
    public function innerJoin($join, $params = array()){
        if(is_array($params)){
            $this->_sqlParams['join'] = array_merge($this->_sqlParams['join'], $params);
        }else {
            $this->_sqlParams['join'][] = $params;
        }
        return $this->_addSqlPart('join', 'INNER JOIN ' . $join, true);
    }
    
    public function leftJoin($join, $params = array()){
        if(is_array($params)){
            $this->_sqlParams['join'] = array_merge($this->_sqlParams['join'], $params);
        }else {
            $this->_sqlParams['join'][] = $params;
        }
        return $this->_addSqlPart('join', 'LEFT JOIN ' . $join, true);
    }
	
	protected function _addSqlPart($queryPartName, $queryPart, $append = false){
        if($append){
            $this->_sqlParts[$queryPartName][] = '
				' . $queryPart;
        }else{
            $this->_sqlParts[$queryPartName] = array($queryPart);
        }
		return $this;
	}

	protected function _removeSqlPart($queryPartName){
		unset($this->_sqlParts[$queryPartName]);
		return $this;
	}
	
	public function debug($on = true, $stop = true){
		if($on) $this->_action_debug = true;
		$this->_action_debug_stop = $stop;
		return $this;
	}
	
	public function scalar($on = true){
		if($on) $this->_action_type = self::ACTION_SCALAR;
		return $this;
	}
	
	public function mixed($mixed = true){
		if($mixed) $this->_mixed_entity = true;
		return $this;
	}
	
	public function entity($entity){
		$this->_entityName = $entity;
		return $this;
	}
	
	public function getEntityName(){
		return $this->_entityName;
	}
	
	public function createEntity($arr = array()){
		$class = KDGInflector::classify($this->getEntityName());
		if(!class_exists($class)) KDGLoader::loadEntityClass($class);
		if(class_exists($class)){
			return new $class($arr);
		} return null;
	}
	
	public function getEntityPrimaryKey(){
		$e = $this->createEntity();
		if($e) return $e->getPrimaryKeyName();
		return 'id';
	}

	public function getSql(){
		$this->prepareQuery();
		$sql = $this->_sql;
		$this->clearSql();
		return $sql;
	}
	
	protected function clearSql(){$this->_sql = '';}
	
	public function custom($sql){
		$this->_sql = $sql;
		$this->execute(false);
	}
	
	public function execute($prepareQuery = true){
		if($prepareQuery) $this->prepareQuery();
		if($this->_action_debug) p($this->_sql, $this->_action_debug_stop);
		//p($this->_sql);
		$result = parent::execute($this->_sql);
		if($this->_action_type == self::ACTION_INSERT){
			if($result){
				return $this->get_insert_id();
			} return false;
		}elseif($this->_action_type == self::ACTION_SELECT){
			if(is_resource($result)){
				//getDiffMemory('m1');
				$numRows = mysqli_num_rows($result);
				//getDiffMemory('m2');
				if($this->_is_count_request){
					$res = mysqli_fetch_field($result);
					return $numRows;
				}
				if($numRows == 0){
					return array();
				}/*elseif($numRows == 1){
					$row = mysqli_fetch_assoc($result);
					$this->_collection[0] = new $this->_entityName($row);
					return $this->_collection;
				}*/else{
					//$i = 1;
					while($row = mysqli_fetch_assoc($result)){
						if($this->_resultType == self::RESULTS_AS_OBJECT){
							if($this->_mixed_entity) $row['kdg_mixed_entity'] = true;
							$this->_collection[] = $this->createEntity($row);
						}else{
							$this->_collection[] = $row;
						}
						/*if($i%1000 == 0){
							getDiffMemory('m3:[' . $i . ']');
						} $i++;*/
					} return $this->_collection;
					//while ($row = mysqli_fetch_assoc($result)){
					//	$this->_collection[] = $row;
					//} return $this->_collection;
				}
			} return false;
		}elseif($this->_action_type == self::ACTION_COUNT){
			//return mysqli_affected_rows($this->link);
			return mysqli_num_rows($result);
		}elseif($this->_action_type == self::ACTION_SCALAR){
			$res = mysqli_fetch_row($result);
			return isset($res[0]) ? $res[0] : null;
		} else return $result;
	}
	
	public function free(){}
	
	public function count($field = '*', $scalar = true){
		if($scalar){
			$this->_is_count_request = true;
			$this->_removeSqlPart('groupby');
			return $this->select('DISTINCT count('.$field.')')->scalar()->execute();
		}else{
			$this->_action_type = self::ACTION_COUNT;
			return $this->execute();
		}
		
	}
	
	public function fetchOne(){
		$this->limit(1);
		$objects = $this->execute();
		if(isset($objects[0])){
			return $objects[0];
		} return null;
	}
	/*
	protected function getEntityName($tableName = false){
		$fromColl = explode(',', $this->_sqlParts['from']);
		if(count($fromColl) > 0){
			$mainFrom = trim($fromColl[0]);
			if($tableName) return $mainFrom;
			$mainFromColl = explode('_', $mainFrom);
			foreach($mainFromColl as $k => $v) $mainFromColl[$k] = ucfirst($v);
			return join('', $mainFromColl);
		} return false;
	}
	*/
	protected function resetCollection(){
		$this->_collection = array();
	}

    protected function _hasSqlPart($queryPartName){
        return count($this->_sqlParts[$queryPartName]) > 0;
    }
	
	protected function prepareQuery(){
        $q = '';
        $fixedParams = array();
        
        if($this->_action_type == self::ACTION_SELECT || $this->_action_type == self::ACTION_COUNT || $this->_action_type == self::ACTION_SCALAR){
        	$q .= (!empty($this->_sqlParts['select'])) ? '' : 'SELECT *';
        	$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'select', '');
        	$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'from');
        }elseif($this->_action_type == self::ACTION_UPDATE){
            $q .= 'UPDATE';
            $q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'from', ' ');
            $q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'set');
            $this->_action_type == self::ACTION_UPDATE;
        }elseif($this->_action_type == self::ACTION_DELETE){
            $q .= 'DELETE';
            $q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'from');
        }
        
        $q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'join', ' ', ' ');
        
        if(!empty($this->_sqlParts['from'])){
        	if(!is_array($this->_sqlParts['from'])) $this->_sqlParts['from'] = array($this->_sqlParts['from']);
        	$fromParts = explode(' ', $this->_sqlParts['from'][0]);
			if(is_null($this->_entityName)){
				$this->_entityName = $fromParts[0];
			}
        }
        
        if(!empty($this->_sqlParams['from'])) $fixedParams = array_merge($fixedParams, $this->_sqlParams['from']);
		if(!empty($this->_sqlParts['set'])) $fixedParams = array_merge($fixedParams, $this->_sqlParams['set']);
        
		$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'where', '', ' ');
		$fixedParams = array_merge($fixedParams, $this->_sqlParams['where']);
		$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'groupby', 'GROUP BY');
		$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'having', '', ' AND ');
		$fixedParams = array_merge($fixedParams, $this->_sqlParams['having']);
		if($this->_action_type != self::ACTION_COUNT){
			$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'orderby', 'ORDER BY');
		}
		$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'limit', '', ' ');
		$q .= KDGInflector::implodeSqlPart($this->_sqlParts, 'offset', '', ' ', 'limit');

        foreach($fixedParams as $k => $v) $fixedParams[$k] = $this->quoteValue($v);
        
        $q = $this->replacePlaceholders($q, $fixedParams);
        
        $this->_sql = $q;
	}

	protected function generateFields(KDGEntity &$object){
		$arr = array();
		foreach($object->toArray() as $field => $value){
			//if($field == $object->getPrimaryKeyName()) continue;
			$arr[] = '`' . $field . '`';
		} return join(', ', $arr);
	}
	
	protected function generateValues(KDGEntity &$object){
		$arr = array();
		foreach($object->toArray() as $field => $value){
			//if($field == $object->getPrimaryKeyName()) continue;
			$arr[] = $this->quoteValue($value);
		} return join(', ', $arr);
	}
	
	protected function generatePairs($object){
		$arr = array();
		foreach($object->toArray() as $field => $value){
			if($field == $object->getPrimaryKeyName()) continue;
			$arr[] = '`' . $field . '` = ' . $this->quoteValue($value);
		} return join(', ', $arr);
	}
	
	public function insertRecord(KDGEntity $object){
		$this->_action_type = self::ACTION_INSERT;
		$this->prepareInsertQuery($object->getTableName(), $this->generateFields($object), $this->generateValues($object));
		$id = $this->execute(false);
		return $object->id = $id;
	}
	
	public function updateRecord(KDGEntity $object){
		$this->_action_type = self::ACTION_UPDATE;
		$this->prepareUpdateQuery($object->getTableName(), $this->generatePairs($object), $object->getPrimaryKeyNameValue());
		$this->execute(false);
		return $object->getPrimaryKeyValue();
	}
	
	public function deleteRecord(KDGEntity $object){
		$this->_action_type = self::ACTION_DELETE;
		$this->prepareDeleteQuery($object->getTableName(), $object->getPrimaryKeyNameValue());
		$this->execute(false);
	}
	
	protected function prepareInsertQuery($table, $fields, $values){
		$this->_sql = 'INSERT INTO `' . $table . '` (' . $fields . ')' . ' VALUES (' . $values . ')';
	}
	
	protected function prepareUpdateQuery($table, $fvpairs, $idField){
		$this->_sql = 'UPDATE `' . $table . '` SET ' . $fvpairs . ' WHERE `' . $idField[0] . '` = ' . intval($idField[1]);
	}
	
	protected function prepareDeleteQuery($table, $idField){
		$this->_sql = 'DELETE FROM `' . $table . '` WHERE `' . $idField[0] . '` = ' . intval($idField[1]);
	}

	protected function replacePlaceholders($string, $params){
		return vsprintf(str_replace(array('%', '?'), array('%%', '%s'), $string), $params);
	}
	
	protected function quoteValue($value){
		$typ = gettype($value);
		if($typ == 'string'){
			return $this->qstr($value);
		}elseif($typ == 'double'){
			// locales fix so 1.1 does not get converted to 1,1
			return str_replace(',','.',$value);
		}elseif($typ == 'boolean'){
			return $value ? 1 : 0;
		}elseif($value === null){
			return 'NULL';
		}else{
			return $value;
		}
	}
	
	protected function qstr($s, $magic_quotes=false){
		if(!$magic_quotes){
			if($this->replaceQuote[0] == '\\'){
				$s = str_replace(array('\\', "\0"), array('\\\\', "\\\0"), $s);
			} return "'" . str_replace("'", $this->replaceQuote, $s) . "'";
		}
		// undo magic quotes for "
		$s = str_replace('\\"', '"', $s);
		if($this->replaceQuote == "\\'")  // ' already quoted, no need to change anything
			return "'$s'";
		else{
			// change \' to '' for sybase/mssql
			$s = str_replace('\\\\', '\\', $s);
			return "'" . str_replace("\\'", $this->replaceQuote, $s) . "'";
		}
	}

}
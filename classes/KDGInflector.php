<?php 

class KDGInflector extends Doctrine_Inflector {

	public static function implodeSqlPart($sqlParts, $sqlPartName, $replacement = '', $glue = ', ', $condition = ''){
    	if(empty($condition) || !empty($sqlParts[$condition]))
    	if(!empty($sqlParts[$sqlPartName])){
    		$replacement = empty($replacement) ? strtoupper($sqlPartName) : strtoupper($replacement);
    		return '
				' . $replacement . ' ' . implode($glue, $sqlParts[$sqlPartName]);
    	} return '';
    }
    
}

?>
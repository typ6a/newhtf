<?php

KDGLoader::loadLibraryClass('KDGModel');

class CategoryModel extends KDGModel {

	public static function getQuery(){
		return KDGDatabase::create()->entity('category')->from('category ca');
	}
	
	public static function findAll(){
		return self::getQuery()->execute();
	}
    
	public static function findAllMain(){
		return self::getQuery()->where('ca.parent_id IS NULL')->execute();
	}
    
	public static function findAllMiddle(){
		return self::getQuery()
            ->select('ca.*')
            ->innerJoin('category ca2 ON ca2.id = ca.parent_id')
            ->where('ca2.parent_id IS NULL')->execute();
	}
    
	public static function findAllSub(){
		return self::getQuery()
            ->select('ca.*')
            ->innerJoin('category ca2 ON ca2.id = ca.parent_id')
            ->innerJoin('category ca3 ON ca3.id = ca2.parent_id')
            ->where('ca3.parent_id IS NULL')->execute();
	}
    
	public static function findOneById($id){
        return self::getQuery()
            ->addWhere('ca.id = ?', $id)->fetchOne();
    }
	
	public static function findOneByTitle($title){
		return self::getQuery()->where('ca.title = ?', $title)->fetchOne();
	}
	
	public static function findOneByUrl($url){
		return self::getQuery()->debug(false)->where('ca.url = ?', $url)->fetchOne();
	}
	
}
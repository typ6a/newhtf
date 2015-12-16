<?php

KDGLoader::loadLibraryClass('KDGModel');

class ProductModel extends KDGModel {

	public static function getQuery(){
		return KDGDatabase::create()->entity('product')->from('product p');
	}
	
	public static function findAll(){
		return self::getQuery()->execute();
	}
    
	public static function findOneById($id){
        return self::getQuery()
            ->addWhere('p.id = ?', $id)->fetchOne();
    }
	
	public static function findOneByTitle($title){
		return self::getQuery()->where('p.title = ?', $title)->fetchOne();
	}
	
	public static function findOneByUrl($url){
		return self::getQuery()->debug(false)->where('p.url = ?', $url)->fetchOne();
	}
	
}
<?php

KDGLoader::loadLibraryClass('KDGModel');

class PropertyModel extends KDGModel {

	public static function getQuery(){
		return KDGDatabase::create()->entity('product_property')->from('product_property pp');
	}
	
	public static function findAll(){
		return self::getQuery()->execute();
	}
    
	public static function findOneById($product_property_id){
        return self::getQuery()
            ->addWhere('pp.id = ?', $product_property_id)->fetchOne();
    }
	
	public static function findOneByTitle($title){
		return self::getQuery()->where('pp.name = ?', $title)->fetchOne();
	}
	
}
<?php

KDGLoader::loadLibraryClass('KDGModel');

class ProductImageModel extends KDGModel {

	public static function getQuery(){
		return KDGDatabase::create()->entity('product_image')->from('product_image pi');
	}
	
	public static function findAll(){
		return self::getQuery()->execute();
	}
    
	public static function findOneByProductId($product_property_id){
        return self::getQuery()
            ->addWhere('pi.id = ?', $product_property_id)->fetchOne();
    }
	
	public static function findOneByUrl($url){
		return self::getQuery()->where('pi.url = ?', $url)->fetchOne();
	}
	
}
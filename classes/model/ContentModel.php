<?php

KDGLoader::loadLibraryClass('KDGModel');

class ContentModel extends KDGModel {

	public static function getQuery(){
		return KDGDatabase::create()->entity('content')->from('content c');
	}
	
	public static function findAll(){
		return self::getQuery()->execute();
	}
    
	public static function findAllGames($limit = 0){
        $query = self::getQuery()
            ->addWhere('c.parent_id is null');
        if($limit > 0){
            $query->limit($limit);
        }
        return $query->execute();
    }
    
	public static function getTotalInCategory($content_id, $category_id){
        return self::getQuery()
            ->addWhere('c.parent_id = ?', $content_id)
            ->addWhere('c.category_id')
            ->count();
    }
    
	public static function findAllXbox360($limit = 0){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 1)
            ->addWhere('c.parent_id is null');
        if($limit > 0){
            $query->limit($limit);
        }
        return $query->execute();
    }
    
	public static function getWithoutPriceTotal(){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 1)
            ->addWhere('c.parent_id is null')
            ->addWhere('c.processed = ?', 0)
            ->addWhere('(c.price is null OR c.price = "" OR c.price = "0" OR c.price = "0.00")');
        return $query->count();
    }
    
	public static function findWithoutPrice($offset = null, $limit = null){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 1)
            ->addWhere('c.parent_id is null')
            ->addWhere('c.processed = ?', 0)
            ->addWhere('(c.price is null OR c.price = "" OR c.price = "0" OR c.price = "0.00")');
		if(!is_null($offset) && !is_null($limit)){
			$query->offset($offset);
			$query->limit($limit);
		} return $query->execute();
    }
    
	public static function getCountByCategoryIdAndContentId($category_id, $content_id){
        return self::getQuery()
            ->addWhere('c.parent_id = ?', $content_id)
            ->addWhere('c.category_id = ?', $category_id)
            ->count();
    }
    
	public static function findAllXbox360Full($limit = 0){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 1)
            ->addWhere('c.parent_id is null');
        if($limit > 0){
            $query->limit($limit);
        }
        return $query->execute();
    }
    
	public static function findAllXboxOneFull($limit = 0){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 2)
            ->addWhere('c.parent_id is null');
        if($limit > 0){
            $query->limit($limit);
        }
        return $query->execute();
    }
    
	public static function findAllXboxOneDLCsNotProcessedTotal(){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 2)
            ->addWhere('c.processed = ?', 0)
            ->addWhere('c.parent_id is not null');
        return $query->count();
    }
    
	public static function findAllXboxOneDLCsNotProcessed($offset = null, $limit = null){
        $query = self::getQuery()
            ->addWhere('c.platform_id = ?', 2)
            ->addWhere('c.processed = ?', 0)
            ->addWhere('c.parent_id is not null');
		if(!is_null($offset) && !is_null($limit)){
			$query->offset($offset);
			$query->limit($limit);
		} return $query->execute();
    }
    
	public static function findOneById($content_id){
        return self::getQuery()
            ->addWhere('c.id = ?', $content_id)->fetchOne();
    }
    
	public static function findGameDLCsByContentId($content_id){
        return self::getQuery()
            ->addWhere('c.platform_id = ?', 1)
            ->addWhere('c.parent_id = ?', $content_id)
            ->addWhere('c.category_id = ?', 1)
            ->execute();
    }
	
	public static function findDLCsByContentId($content_id){
        return self::getQuery()
            ->addWhere('(c.parent_id = ?)', array($content_id))
            ->orderBy('c.release_date DESC')
            ->execute();
    }
	
	public static function findAddonsByContentId($content_id){
        return self::getQuery()
            ->addWhere('c.parent_id = ?', $content_id)
            ->addWhere('c.category_id = ?', 3)
            ->execute();
    }
	
	public static function getDLCsCountByContentId($content_id){
        return self::getQuery()
            ->addWhere('(c.parent_id = ?)', array($content_id))
            ->count();
    }
	
	public static function findOneByTitle($title){
		return self::getQuery()->where('c.title = ?', $title)->fetchOne();
	}
	
	public static function findOneByUrl($url){
		return self::getQuery()->where('c.url = ?', $url)->fetchOne();
	}
	
	public static function getNotProcessedQuery(){
		return self::getQuery()->addWhere('c.processed = ?', 0)->addWhere('c.platform_id = ?', 1)->addWhere('c.parent_id IS NULL');
	}
	
	public static function getNotProcessedTotal(){
		return self::getNotProcessedQuery()->count();
	}
	
	public static function findNotProcessed($offset = null, $limit = null){
		$query = self::getNotProcessedQuery();
		if(!is_null($offset) && !is_null($limit)){
			$query->offset($offset);
			$query->limit($limit);
		} return $query->execute();
	}
	
	public static function getXboxOneNotProcessedQuery(){
		return self::getQuery()->addWhere('c.processed = ?', 0)->addWhere('c.platform_id = ?', 2)->addWhere('parent_id is null');
	}
	
	public static function getXboxOneNotProcessedTotal(){
		return self::getXboxOneNotProcessedQuery()->count();
	}
	
	public static function findXboxOneNotProcessed($offset = null, $limit = null){
		$query = self::getXboxOneNotProcessedQuery();
		if(!is_null($offset) && !is_null($limit)){
			$query->offset($offset);
			$query->limit($limit);
		} return $query->execute();
	}
    
	public static function getFacebookNotProcessedQuery(){
		return self::getQuery()->addWhere('c.platform_id = ?', 1)->addWhere('c.facebook_likes_count IS NULL');
	}
	
	public static function getFacebookNotProcessedTotal(){
		return self::getFacebookNotProcessedQuery()->addWhere('c.parent_id IS NULL')->count();
	}
	
	public static function findFacebookNotProcessed($offset = null, $limit = null){
		$query = self::getFacebookNotProcessedQuery();
		if(!is_null($offset) && !is_null($limit)){
			$query->offset($offset);
			$query->limit($limit);
		} return $query->execute();
	}
    
	public static function deleteByContentId($content_id){
        return self::getQuery()->delete('content')->addWhere('parent_id = ?', $content_id)->execute();
    }
	
	public static function getFirstLastAddonDays($content_id){
        return self::getQuery()
            ->select(join(', ', array(
                'DATEDIFF(MIN(c2.release_date), c.release_date) as first_addon_days',
                'DATEDIFF(MAX(c2.release_date), c.release_date) as last_addon_days'
            )))
            ->from('content c')
            ->innerJoin('content c2 on c2.parent_id = c.id')
            ->addWhere('c.id = ?', $content_id)
            ->addWhere('c2.category_id = ?', 3)
            ->addWhere('c2.release_date >= c.release_date')
            ->mixed()
            //->debug()
            ->fetchOne();
    }
    
    
	public static function deleteBundleItemsByContentId($content_id){
        return self::getQuery()->delete('content')->addWhere('category_id = ?', 9)->addWhere('parent_id = ?', $content_id)->execute();
    }
	
}
<?php

KDGLoader::loadEntityClass('Product');
KDGLoader::loadModelClass('ProductModel');
KDGLoader::loadModelClass('CategoryModel');

KDGLoader::loadLibraryClass('parsers/NewhtfProductsListParser');

class NewhtfCollectProductsCrawler extends Crawler {
    
    protected function crawl(){
        $categories = CategoryModel::findAllMain();
        foreach ($categories as $category) {
            $html = $this->requestProductsListPage($category->url);
            if($html){
                $this->parseProductsList($html, $category);
            }
        }
    }
    
    protected function requestProductsListPage($url){
		$this->sendBuffered('category: ' . $url);
		return $this->makeRequest($url, false, false, false);
	}
    
	protected function parseProductsList($html, $category){
		$parser = new NewhtfProductsListParser($html);
        foreach ($parser->products_list as $data) {
            $entity = ProductModel::findOneByUrl($data['url']);
            if(!$entity){
                $entity = new Product();
            }
            $data['category_id'] = $category->id;
            $entity->fromArray($data);
            $entity->save();
            $this->sendBuffered('product: ' . $entity->url);
        }
        //$category->processed = 1;
        //$category->save();
	}
	
}
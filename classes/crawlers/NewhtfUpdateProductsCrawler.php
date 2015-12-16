<?php

KDGLoader::loadEntityClass('Product');
KDGLoader::loadModelClass('ProductModel');


KDGLoader::loadLibraryClass('parsers/NewhtfUpdateProductParser');

class NewhtfUpdateProductsCrawler extends Crawler {
    
    protected function crawl(){
        $products_list = ProductModel::findAll();
        foreach ($products_list as $product) {
            
           // pre($product,1);
            
            $html = $this->requestProductPage($product->url);
            
                       
            if($html){
                $this->parseProduct($html, $product);
            }
        }
    }
    
    protected function requestProductPage($url){
		$this->sendBuffered('category: ' . $url);
		return $this->makeRequest($url, false, false, false);
	}
    
	protected function parseProduct($html, $product){
		$parser = new NewhtfUpdateProductParser($html, $product);
        foreach ($parser->product as $data) {
            $entity = ProductUpdateModel::findOneByUrl($data['url']);
            if(!$entity){
                $entity = new Product();
            }
            $data['category_id'] = $product->id;
            $entity->fromArray($data);
            $entity->save();
            $this->sendBuffered('product: ' . $entity->url);
        }
        //$category->processed = 1;
        //$category->save();
	}
	
}
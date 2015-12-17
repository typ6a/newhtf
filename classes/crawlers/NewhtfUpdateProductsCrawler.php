<?php

KDGLoader::loadEntityClass('Product');
KDGLoader::loadModelClass('ProductModel');


KDGLoader::loadLibraryClass('parsers/NewhtfUpdateProductParser');

class NewhtfUpdateProductsCrawler extends Crawler {
    
    protected function crawl(){
        $products = ProductModel::findAll();
        foreach ($products as $product) {
            sleep(0.2);
            $html = $this->requestProductPage($product->url);
            if($html){
                $this->parseProduct($html, $product);
            }
        }
    }
    
    protected function requestProductPage($url){
		$this->sendBuffered('[product url]: ' . $url);
		return $this->makeRequest($url, false, false, false);
	}
    
	protected function parseProduct($html, $product){
		new NewhtfUpdateProductParser($html, $product);
	}
	
}
<?php

KDGLoader::loadEntityClass('Category');
KDGLoader::loadModelClass('CategoryModel');

KDGLoader::loadLibraryClass('parsers/NewhtfMainCategoryParser');
KDGLoader::loadLibraryClass('parsers/NewhtfMiddleCategoryParser');
KDGLoader::loadLibraryClass('parsers/NewhtfSubCategoryParser');

class NewhtfCategoryCrawler extends Crawler {
    
    protected $mainCategories = [];
    protected $middleCategories = [];
    protected $subCategories = [];
    
    protected function crawl(){}
    
    /* MAIN CATEGORIES */
    public function crawlMainCategories(){
		$this->response = null;
		$this->requestMainCategoriesPage();
		if($this->response){
			$this->parseMainCategories();
		}
    }
    
    protected function requestMainCategoriesPage(){
		$url = 'http://newhtf.ru/catalog/';
		getDiffTime(1);
		getDiffMemory(1);
		$this->sendBuffered($url);
		$this->response = $this->makeRequest($url, false, false, false);
	}
    
	protected function parseMainCategories(){
		$parser = new NewhtfMainCategoryParser($this->response);
        foreach ($parser->categories as $c_data) {
            $category_entity = CategoryModel::findOneByUrl($c_data['url']);
            if(!$category_entity){
                $category_entity = new Category();
            }
            $category_entity->fromArray($c_data);
            $category_entity->save();
        }
	}
    
    /* MIDDLE CATEGORIES */
    public function crawlMiddleCategories(){
        $mainCategories = CategoryModel::findAllMain();
        foreach($mainCategories as $mainCategory){
            sleep(1);
            $response = $this->requestMiddleCategoriesPage($mainCategory->url);
            if($response){
                $this->parseMiddleCategories($response, $mainCategory->id);
            }
        }
    }
    
    protected function requestMiddleCategoriesPage($url){
		getDiffTime(1);
		getDiffMemory(1);
		$this->sendBuffered($url);
		return $this->makeRequest($url, false, false, false);
	}
    
	protected function parseMiddleCategories($response, $parent_id){
		$parser = new NewhtfMiddleCategoryParser($response);
        foreach ($parser->categories as $c_data) {
            $category_entity = CategoryModel::findOneByUrl($c_data['url']);
            if(!$category_entity){
                $category_entity = new Category();
            }
            $c_data['parent_id'] = $parent_id;
            $category_entity->fromArray($c_data);
            $category_entity->save();
        }
	}
    
    /* SUB CATEGORIES */
    public function crawlSubCategories(){
        $middleCategories = CategoryModel::findAllMiddle();
        foreach($middleCategories as $middleCategory){
            sleep(0.5);
            $response = $this->requestSubCategoriesPage($middleCategory->url);
            if($response){
                $this->parseSubCategories($response, $middleCategory->id);
            }
        }
    }
    
    protected function requestSubCategoriesPage($url){
		getDiffTime(1);
		getDiffMemory(1);
		$this->sendBuffered($url);
		return $this->makeRequest($url, false, false, false);
	}
    
	protected function parseSubCategories($response, $parent_id){
		$parser = new NewhtfSubCategoryParser($response);
        foreach ($parser->categories as $c_data) {
            $category_entity = CategoryModel::findOneByUrl($c_data['url']);
            if(!$category_entity){
                $category_entity = new Category();
            }
            $c_data['parent_id'] = $parent_id;
            $category_entity->fromArray($c_data);
            $category_entity->save();
        }
        
	}
	
	
}
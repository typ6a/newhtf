<?php

//KDGLoader::loadModelClass('ProductModel');
//KDGLoader::loadEntityClass('MainCategory');
//KDGLoader::loadEntityClass('Category');
//KDGLoader::loadEntityClass('SubCategory');
//KDGLoader::loadModelClass('MainCategoryModel');
//KDGLoader::loadModelClass('CategoryModel');
//KDGLoader::loadModelClass('SubCategoryModel');

class NewhtfMiddleCategoryParser extends KDGParser {

    public $categories = [];
    
    protected function parse() {
        $items = $this->getResponseObject()->query('//*[@class="ys_catalog_text_ul"]/li/*/a[2]');
        for ($i = 0; $i < $items->length; $i++) {
            $item = $items->item($i);
            $category_name = $item->nodeValue;
            $category_url = $item->getAttribute('href');
            $this->categories[$category_name]['title'] = $category_name;
            $this->categories[$category_name]['url'] = 'http://newhtf.ru' . $category_url;
            
        }
    }

}






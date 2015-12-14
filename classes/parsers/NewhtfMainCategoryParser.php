<?php

//KDGLoader::loadModelClass('ProductModel');
//KDGLoader::loadEntityClass('MainCategory');
//KDGLoader::loadEntityClass('Category');
//KDGLoader::loadEntityClass('SubCategory');
//KDGLoader::loadModelClass('MainCategoryModel');
//KDGLoader::loadModelClass('CategoryModel');
//KDGLoader::loadModelClass('SubCategoryModel');

class NewhtfMainCategoryParser extends KDGParser {

    protected $RawItem = null;

    protected function parseDataObject() {
        
    }

    protected function hasDataObject() {
        return true;
    }

    protected function parseMainCategory() {
        $mainCategories = [];
        $items = $this->getResponseObject()->query('//div[@class="tit"]/a');

        for ($i = 0; $i < $items->length; $i++) {
            $item = $items->item($i);
            $main_category_name = $item->nodeValue;
            $main_category_url = $item->getAttribute('href');
            $mainCategories[$main_category_name] = $main_category_url;
        }
        return $mainCategories;
    }

    protected function parse() {

        $data['main_category'] = $this->parseMainCategory();

        foreach ($data['main_category'] as $mcname => $curl) {
            $html = file_get_contents($curl);
        }

        pre('and of parse method for category parser!!!', 1);
        // дальше не понятно
        $mainCategories = new MainCategory();
        $mainCategories->fromArray($data);
        $main_category_id = $mainCategories->save();

        if ($main_category_id) {
            $mainCategories = $this->parseMainCategory();
           
        }
    }

}

pre('and of parse method for main category parser!!!', 1);





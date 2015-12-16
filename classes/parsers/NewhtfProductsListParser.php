<?php

class NewhtfProductsListParser extends KDGParser {

    public $products_list = [];
    
    protected function parse() {
        $items = $this->getResponseObject()->query('//ul/li/div/div[@class="sl_info"]/h3/a');
        for ($i = 0; $i < $items->length; $i++) {
            $item = $items->item($i);
            $product_name = $item->nodeValue;
            $product_url = $item->getAttribute('href');
            $this->products_list[$product_name]['title'] = $product_name;
            $this->products_list[$product_name]['url'] = 'http://newhtf.ru' . $product_url;
            
        }
    }

}






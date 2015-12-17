<?php

KDGLoader::loadEntityClass('Product');
KDGLoader::loadEntityClass('ProductProperty');
KDGLoader::loadEntityClass('ProductToProperty');
KDGLoader::loadModelClass('PropertyModel');

KDGLoader::loadEntityClass('ProductImage');
KDGLoader::loadModelClass('ProductImageModel');

class NewhtfUpdateProductParser extends KDGParser {

    protected $product = null;

    public function __construct($response, $product){
        $this->product = $product;
        parent::__construct($response);
    }
    
    protected function parseDataObject() {
        
    }

    protected function hasDataObject() {
        return true;
    }

    protected function parseTitle() {
        return $this->getResponseObject()->query('//h1')->item(0)->nodeValue;
    }

    protected function parsePrice() {
        $items = $this->getResponseObject()->query('//span[@id="ys_top_price"]/span[@class="allSumMain"]');
        if($items->length > 0){
            $item = $items->item(0);
            return str_replace(' ', '', $item->nodeValue);
        }
    }

    protected function parseImages() {
        $images = [];
        $items = $this->getResponseObject()->query('//div[@class="item_gal"]/a/img');
        for ($i = 0; $i < $items->length; $i++) {
            $src = $items->item($i)->getAttribute('src');
            $src = str_replace('/6_', '/7_', $src);
            $src = 'http://' . trim($src, '/');
            $images[] = $src;
        }
        $this->saveImages($images);
    }

    protected function saveImages($images) {
        if (count($images) > 0) {
            foreach ($images as $key => $url) {
                $image_entity = ProductImageModel::findOneByUrl($url);
                if (!$image_entity) {
                    
                    // save image to local HDD
                    $filename = 'p_' . $this->product->id . '_i_' . $key . '.jpg';
                    $filepath = 'd:\workspace\newhtf\data\images\\' . $filename;
                    $image_bin = file_get_contents($url);
                    file_put_contents($filepath, $image_bin);
                    
                    // save image to DB
                    if(file_exists($filepath)){
                        $image_entity = new ProductImage();
                        $image_entity->fromArray([
                            'product_id' => $this->product->id,
                            'url' => $url,
                            'filename' => $filename
                        ]);
                        $image_entity->save();
                    }
                } else {
                    $image_entity->id;
                }
            }
        }
    }
    
    protected function parseProperties() {
        $raw = $this->getResponseObject()->query('//div[@class="yeni_ipep_props_groups"]/table/tbody/tr/td');
        $properties = [];
        for ($i = 0; $i < $raw->length; $i++) {
            if ($i % 2 === 0) {
                $property_name = trim($raw->item($i)->nodeValue);
                $property_value = trim($raw->item($i + 1)->nodeValue);
                $properties[$property_name] = $property_value;
            } else {
                continue;
            }
        }
        $this->saveProperties($properties);
    }

    protected function saveProperties($properties) {
        if (count($properties) > 0) {
            foreach ($properties as $pname => $pvalue) {
                $property = PropertyModel::findOneByTitle($pname);
                if (!$property) {
                    $property = new ProductProperty();
                    $property->fromArray([
                        'name' => $pname
                    ]);
                    $property_id = $property->save();
                } else {
                    $property_id = $property->id;
                }
                if ($property_id) {
                    $ptp = new ProductToProperty();
                    $ptp->fromArray([
                        'product_id' => $this->product->id,
                        'product_property_id' => $property_id,
                        'value' => $pvalue
                    ]);
                    $ptp->save();
                }
            }
        }
    }
    
    protected function parse() {
        
        //$data['title'] = $this->parseTitle();
        
        $this->parseImages();
        $this->parseProperties();

        $this->product->price = $this->parsePrice();
        $this->product->processed = 1;
        
        $this->product->save();

    }

}

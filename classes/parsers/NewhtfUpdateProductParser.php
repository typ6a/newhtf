<?php

KDGLoader::loadEntityClass('Product');
KDGLoader::loadEntityClass('ProductProperty');
KDGLoader::loadEntityClass('ProductToProperty');
KDGLoader::loadModelClass('PropertyModel');

//KDGLoader::loadModelClass('ProductModel');

class NewhtfUpdateProductParser extends KDGParser {

    protected $RawItem = null;
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
        return str_replace(' ', '', $this->getResponseObject()->query('//span[@id="ys_top_price"]/span[@class="allSumMain"]')->item(0)->nodeValue);
    }

    protected function parseImages() {
        $images = [];
        $items = $this->getResponseObject()->query('//div[@class="item_gal"]/a/img');
        for ($i = 0; $i < $items->length; $i++) {
            $src = $items->item($i)->getAttribute('src');
            $src = str_replace('/6_', '/7_', $src);
            $src = 'http://' . trim($src, '/');
            //$image_bin = file_get_contents($src);
            //file_put_contents('d:\workspace\newhtf\data\images\image_number_' . $i . '.jpg', $image_bin);
            $images[] = $src;
        }
        return $images;
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
        return $properties;
    }

    protected function parse() {
        //$data['title'] = $this->parseTitle();
        $data['price'] = $this->parsePrice();
        
        $product = ProductModel::findOneByUrl($this->product->url);
        if (!$product) {
            $product = new Product();
        }
        $product->fromArray($data);
        $new_product_id = $product->save();

        if ($new_product_id) {
            $images = $this->parseImages();
            $properties = $this->parseProperties();

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
                            'product_id' => $new_product_id,
                            'product_property_id' => $property_id,
                            'value' => $pvalue
                        ]);
                        pre($product, 1);
                        $ptp->save();
                    }
                }
            }
        }

        pre('probably all saved!!!', 1);
    }

}

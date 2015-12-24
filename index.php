<?php

require_once 'include/config.php';


KDGLoader::loadEntityClass('Product');
KDGLoader::loadModelClass('ProductModel');

//$products = ProductModel::findAll();

$products = [];

$i = 1;
while($i <= 10){
    $products[] = [
        'id' => $i,
        'category_id' => 1,
        'title' => 'Product #' . $i,
        'price' => $i * rand(2, 5),
        'processed' => 1
    ];
    
    $i++;
}

include_once './tpl/products.php';
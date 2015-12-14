<?php

require_once 'include/config.php';

set_time_limit(0);
ini_set('memory_limit', '256M');


//KDGLoader::loadLibraryClass('parsers/NewhtfProductParser');
KDGLoader::loadLibraryClass('parsers/NewhtfMainCategoryParser');

$product_url = 'http://newhtf.ru/catalog/paneli-interernye-svetilniki/svetilniki_serii_kub/svetodiodnyy_svetilnik_dlya_interera_htf_cub_6_plt_6w_nw.html';

$category_url = 'http://newhtf.ru/catalog/';

$mainCategory_url = 'http://newhtf.ru/catalog/';

//$SubCategory_url = '????????????';

$html = file_get_contents($mainCategory_url);
new NewhtfMainCategoryParser($html);



//$html = file_get_contents($product_url);
//new NewhtfProductParser($html);
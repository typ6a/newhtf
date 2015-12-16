<?php

require_once 'include/config.php';

set_time_limit(0);
ini_set('memory_limit', '256M');

KDGLoader::loadLibraryClass('crawlers/NewhtfUpdateProductsCrawler');

new NewhtfUpdateProductsCrawler();

//$product_url = 'http://newhtf.ru/catalog/paneli-interernye-svetilniki/svetilniki_serii_kub/svetodiodnyy_svetilnik_dlya_interera_htf_cub_6_plt_6w_nw.html';
//KDGLoader::loadLibraryClass('parsers/NewhtfProductParser');
//$html = file_get_contents($product_url);
//new NewhtfProductParser($html);
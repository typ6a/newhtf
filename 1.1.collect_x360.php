<?php

require_once 'include/config.php';

set_time_limit(0);
ini_set('memory_limit', '256M');

KDGLoader::loadLibraryClass('crawlers/CollectXbox360ContentCrawler');
KDGLoader::loadModelClass('PlatformModel');

new CollectXbox360ContentCrawler();
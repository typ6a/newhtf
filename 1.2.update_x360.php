<?php

require_once 'include/config.php';

set_time_limit(0);
ini_set('memory_limit', '256M');

KDGLoader::loadLibraryClass('crawlers/UpdateXbox360ContentCrawler');

$o = getRequestParameter('offset', null);
$l = getRequestParameter('limit', null);

new UpdateXbox360ContentCrawler($o, $l);
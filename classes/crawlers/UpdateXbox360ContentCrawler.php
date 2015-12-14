<?php

KDGLoader::loadLibraryClass('KDGCrawler');
KDGLoader::loadLibraryClass('parsers/Xbox360ContentDetailsPageParser');
KDGLoader::loadModelClass('ContentModel');

class UpdateXbox360ContentCrawler extends Crawler {
	
	protected $offset	= null;
	protected $limit	= null;
	protected $content_object = null;
	
	public function __construct($offset = null, $limit = null){
		$this->offset = $offset;
		$this->limit = $limit;
		parent::__construct();
	}
	
	public function execute(){
		parent::execute();
		getDiffTime('start');
		$objects = ContentModel::findNotProcessed($this->offset, $this->limit);
		//$objects = ContentModel::findWithoutPrice($this->offset, $this->limit);
		//$objects = array(ContentModel::findOneById(4));
		//p(count($objects),1);
		foreach($objects as $this->content_object){
			$this->requestPage();
			$this->parsePage();
		}
		getDiffTime('finish', true);
	}
	
	protected $globalCounter = 1;
	protected function requestPage(){
		$url = $this->content_object->url;
		if($url){
            if(!stristr($url, 'marketplace.xbox.com')){
                $url = 'http://marketplace.xbox.com/' . trim($url, '/');
            }
			$this->sendBuffered($this->content_object->id . ' -> ' . $url);
			if($this->globalCounter%10 == 0){
				getDiffTime($this->content_object->id);
				getDiffMemory($this->content_object->id);
                sleep(rand(2, 5));
			}
			$this->globalCounter++;
			$this->response = $this->makeRequest($url);
		}
	}
	
	protected function parsePage(){
		new Xbox360ContentDetailsPageParser($this->response, $this->content_object);
	}
	
}
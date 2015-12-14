<?php

KDGLoader::loadLibraryClass('parsers/Xbox360ContentPageParser');

class CollectXbox360ContentCrawler extends Crawler {
	
	protected $page		= 1;
	protected $pagemax	= 22;
    
	public function execute(){
        while($this->page <= $this->pagemax){
            $this->collectContent();
            $this->page++;
        }
	}
	
	protected function collectContent(){
		$this->response = null;
		$this->requestPage();
		if($this->response){
			$this->parsePage();
		}
	}
    
    protected function strToHex($string) {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    protected function requestPage(){
		$url = $this->getPageUrl();
		getDiffTime(1);
		getDiffMemory(1);
		$this->sendBuffered($url);
		$this->response = $this->makeRequest($url, false, false, false);
	}
	
	protected function parsePage(){
		new Xbox360ContentPageParser($this->response);
	}
	
	protected function getPageUrl(){
        return 'http://marketplace.xbox.com/en-US/Games/FullGames?PageSize=90&Page=' . $this->page;
        //return 'http://marketplace.xbox.com/en-US/Games?PageSize=90&Page=' . $this->page;
	}
	
}
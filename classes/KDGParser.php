<?php

KDGLoader::loadLibraryClass('vendor/SimpleHtmlDom/simple_html_dom');

abstract class KDGParser {
	
	protected $data				= array();
	protected $errors			= array();
	protected $response			= null;
	protected $response_object	= null;
	protected $data_object		= null;
	
	public function __construct($response){
		set_time_limit(0);
		$this->setResponse($response);
		$result = $this->execute();
		if($result !== true){
			$this->errors[] = $result;
		}
	}
	
	public function hasErrors(){return count($this->getErrors()) ? true : false;}
	public function getErrors(){return $this->errors;}
	
	private function execute(){
		if($this->hasResponse()){
			$this->createResponseObject();
			if($this->hasResponseObject()){
                $this->parse();
                $this->destroy();
                return true;
			} else return 'No Response Object';
		} else return 'No Response';
		$this->destroy();
	}

	abstract protected function parse();
	
	protected function setResponse($response){$this->response = $response;}
	protected function getResponse(){return $this->response;}
	protected function hasResponse(){return !is_null($this->response) ? true : false;}
	
	protected function hasResponseObject(){return $this->getResponseObject() ? true : false;}
	protected function getResponseObject(){return $this->response_object;}
	protected function setResponseObject($object){$this->response_object = $object;}
	protected function createResponseObject(){
        $dom = new DomDocument();
        @$dom->loadHTML($this->response);
        $this->setResponseObject(new DomXPath($dom));
    }
	
	protected function destroy(){
		$this->destroyResponseObject();
		$this->destroyResponse();
	}
	
	protected function destroyResponse(){
		$this->response = null;
	}
	
	protected function destroyResponseObject(){
		if($this->hasResponseObject()){
			if($this->getResponseObject() instanceof simple_html_dom){
				$this->getResponseObject()->clear();
			}
		} $this->setResponseObject(null);
	}
	
	protected function save(){}
	protected function clearData(){$this->data = array();}
	public function getData(){return $this->data;}
	
	protected function cleanData($data = array()){
		if(!$data){
			$this->data = $this->cleanSpaces($this->data);
		} else return $this->cleanSpaces($data);
	}

	protected function cleanSpaces($val = null){
		$is_single_var = false;
		if(!is_array($val)){
			$is_single_var = true;
			$val = array($val);
		}
		foreach($val as $k => $v){
			$val[$k] = (!is_array($v)) ? $this->cleanValue($v, $k) : $this->cleanSpaces($v);
		} return $is_single_var ? $val[0] : $val;
	}

	protected function cleanValue($v, $name = null){
		$v = trim($v);
		if(mb_detect_encoding($v) != 'UTF-8'){
			$v = utf8_encode($v);
		} return preg_replace(array('/\r\n/u', '/\s+|&nbsp;/u'), array(' ', ' '), $v);
	}
	
	protected function sendBuffered($content = null){
		if($content) echo $content.(isConsole() ? "\n" : '</br>');
		@ob_flush();
		@flush();
	}
    
    /**
     * Convert Dom Node to XPath object
     * 
     * @param DOMNode $item
     * @return \DOMXPath
     */
    protected function nodeToXPath(DOMNode $item){
        $dom = new DomDocument;
        $dom->appendChild($dom->importNode($item, true));
        return new DOMXPath($dom);
    }

}
<?php

KDGLoader::loadLibraryClass('vendor/SimpleHtmlDom/simple_html_dom');

class KDGService {
	
	protected $cacheLifetime				= 86400; // 24 hours
	
	protected $data							= array();
	protected $request						= array();
	protected $rawLinks						= array();
	protected $comparedData					= array();

	const SERVICE_SEARCH_STATUS_TRUE		= 1;
	const SERVICE_SEARCH_STATUS_FALSE		= 0;
	const SERVICE_SEARCH_STATUS_ERROR		= 2;
	
	protected $ServiceHost					= null;
	protected $ServiceCode					= null;
	protected $ServiceTitle					= null;
	protected $ServiceMethod				= 'html';
	protected $ServiceEnabled				= true;
	protected $ServiceStatus				= self::SERVICE_SEARCH_STATUS_FALSE; // not found anything by default
	protected $ServiceStatusErrorMessage	= null;
	
	protected $company						= null;
	protected $rawLink						= null;
	protected $rawLinkDom					= null;
	protected $rawLinksDom					= null;
	protected $rawLinksHtml					= null;
	protected $rawLinksLimit				= 5;
	protected $rawLinkRequestAttempt		= 1;

	protected $html_response				= null;
	protected $html_response_object			= null;
	
	public function __construct($request = array()){
		$this->request = $request;
	}
	
	protected function correctRequestData(){
		$this->request['title'] = preg_replace(array(
			'/( ?'.trim($this->request['city']).' ?)/',
			'/('.trim($this->request['state']).')/'
		), array(
			'',
			''
		), $this->request['title']);
	}

	protected function hasCache(){
		$path = $this->getCachePath();
		if(file_exists($path) && is_file($path)){
			if(!$this->isCacheExpired($path)){
				return true;
			}
		} return false;
	}
	
	public function execute(){
		if(!$this->hasCache()){
			if($this->collectRaw()){
				if($this->executeRaw()){
					$this->save();
				} else $this->save(true);
			}elseif(!$this->ServiceStatus){
				$this->save(true);
			}
		}else{
			echo '/*FROM CACHE*/';
			$this->data = $this->getFromCache();
			if(count($this->data)){
				$this->setSearchStatusTrue();
			} else $this->setSearchStatusFalse();
		}
	}
	
	protected function parseRawLinks(){}
	protected function parseRawLinkUrl(){return trim($this->rawLinkDom->href);}
	protected function parseRawLinkTitle(){return trim($this->rawLinkDom->plaintext);}
	
	public function collectRaw(){
		$url = $this->getRequestUrl();
		if($url){
			$this->createRawLinksResponse($url);
			if($this->hasRawLinksResponse()){
				$this->createRawLinksObject();
				if($this->hasRawLinksObject()){
					$rawLinksColl = $this->parseRawLinks();
					if(is_object($rawLinksColl)){
						$rawLinksColl = array($rawLinksColl);
					}
					if(count($rawLinksColl)){
						$rawLinksColl = array_slice($rawLinksColl, 0, $this->rawLinksLimit);
						foreach($rawLinksColl as $this->rawLinkDom){
							$this->parseRaw();
							if($this->hasRawData()){
								if($this->compareRaw()){
									$this->save();
									return false;
								}
							}
							if(!empty($this->raw_data['url'])){
								$this->rawLinks[] = $this->raw_data['url'];
							}
						}
					}
				} else $this->setSearchStatusError('rawLinksDom');
			}
			if(!count($this->rawLinks)){
				$this->rawLinkRequestAttempt++;
				if($this->rawLinkRequestAttempt > 10) exit; //else p($this->rawLinkRequestAttempt);
				return $this->collectRaw();
			}else{
				$this->destroyRaw();
			} return count($this->rawLinks);
		}
	}
	
	protected function executeRaw(){
		if(count($this->rawLinks)){
			foreach($this->rawLinks as $this->rawLink){
				$this->createHtmlResponse($this->rawLink);
				if($this->hasHtmlResponse()){
					$this->createHtmlObject();
					if($this->hasHtmlObject()){
						$this->createDataObject();
						if($this->hasDataObject()){
							if($this->parse()){
								$this->destroy();
								if($this->compare()){ // TODO make compare using result data, without request whole item details page
									return true;
								}
							}
						} else $this->setSearchStatusError('No Data Object');
					} else $this->setSearchStatusError('No Html Object');
				} else $this->setSearchStatusError('No Html Response');
			} 
		} return false;
	}
	
	protected $raw_data = array();
	protected function parseRaw(){
		$data['url'] = $this->parseRawUrl();
		$data['basic'] = $this->parseRawBasicInfo();
		$data['category'] = $this->parseRawCategoriesInfo();
		$data['website'] = $this->parseRawWebSiteInfo();
		$data['photo'] = $this->parseRawPicturesInfo();
		$data['description'] = $this->parseRawDescriptionInfo();
		$data['offer'] = $this->parseRawSpecialOfferInfo();
		$this->setRawData($data);
	}
	
	protected function setRawData($data){$this->raw_data = $data;}
	protected function getRawData(){return $this->raw_data;}
	protected function hasRawData(){
		foreach($this->getRawData() as $key => $el){
			if($key != 'url' && $el) return true;
		} return false;
	}
	
	protected function parseRawUrl(){return $this->parseRawLinkUrl();}
	protected function parseRawCategoriesInfo(){}
	protected function parseRawWebSiteInfo(){}
	protected function parseRawDescriptionInfo(){}
	protected function parseRawPicturesInfo(){}
	protected function parseRawSpecialOfferInfo(){}
	
	protected function parseRawTitle(){}
	protected function parseRawPhone(){}
	protected function parseRawStreet(){}
	protected function parseRawCity(){}
	protected function parseRawState(){}
	protected function parseRawZip(){}
	protected function parseRawLocation(){
		$location['street'] = $this->parseRawStreet();
		$location['city'] = $this->parseRawCity();
		$location['state'] = $this->parseRawState();
		$location['zip'] = $this->parseRawZip();
		return $location;
	}
	protected function parseRawBasicInfo(){
		return array_merge(array(
			'title' => $this->parseRawTitle(),
			'phone' => $this->parseRawPhone(),
		), $this->parseRawLocation());
	}
	
	protected function parse(){
		$this->clearData();
		$this->parseUrl();
		$this->parseBasicInfo();
		$this->parseCategoriesInfo();
		$this->parseWebSiteInfo();
		$this->parsePicturesInfo();
		$this->parseDescriptionInfo();
		$this->parseSpecialOfferInfo();
		return count($this->results()) ? true : false;
	}
	
	protected function parseUrl(){$this->data['url'] = $this->rawLink;}

	protected function parseBasicInfo(){}
	protected function parseCategoriesInfo(){}
	protected function parseWebSiteInfo(){}
	protected function parseDescriptionInfo(){}
	protected function parsePicturesInfo(){}
	protected function parseSpecialOfferInfo(){}
	
	protected function compareRaw(){
		$this->data = $this->getRawData();
		return $this->compare();
	}
	
	protected function compare(){
		$this->cleanData();
		$percentage = 0;
		$percents = array(
			'title' => 25,
			'phone' => 25,
			'street' => 25,
			'city' => 8.3,
			'state' => 8.3,
			'zip' => 8.3,
			'raw_string' => 0,
		);
		foreach($this->request as $field => $value){
			$percentageIncrement = $percents[$field];
			if(!empty($value)){
				$value = self::beforeCompareCleaning($field,$value);
				if(isset($this->data['basic']) && $this->data['basic']){
					foreach($this->data['basic'] as $f => $v){
						$v = self::beforeCompareCleaning($f,$v);
						if($field == $f){
							if(strlen($v) < strlen($value)){
								if(stristr($v, $value)) $percentage += $percentageIncrement;
							}else{
								if(stristr($value, $v)) $percentage += $percentageIncrement;
							}
						}
					}
				}
			}
		}
		if(round($percentage) > 25){
			$this->setSearchStatusTrue();
			return true;
		} return false;
	}

	protected function request(){
		if($this->ServiceMethod == 'api' || $this->ServiceMethod == 'both') $this->requestApi();
		if($this->ServiceMethod == 'html' || $this->ServiceMethod == 'both') $this->requestHtml();
	}
	
	protected function requestApi(){
		if(!$this->hasApiResponse()) $this->createApiResponse();
		if(!$this->hasApiObject()) $this->createApiObject();
	}
	
	protected function requestHtml(){
		if(!$this->hasHtmlResponse()) $this->createHtmlResponse();
		if(!$this->hasHtmlObject()) $this->createHtmlObject();
	}
	protected $globalCounter = 0;
	protected $title_tmp = null;
	protected function getRequestTitle(){return $this->title_tmp ? $this->title_tmp : $this->request['title'];}
	protected function genRequestTitle(){
		if($this->globalCounter > 10){
			exit;
		} else $this->globalCounter++;
		$title = trim($this->request['title']);
		if($title){
			$this->title_tmp = $title;
			if($this->rawLinkRequestAttempt > 1){
				$parts = explode(' ', $title);
				if($parts){
					$sliceLength = $this->rawLinkRequestAttempt-1;
					if($sliceLength < count($parts)){
						$this->title_tmp = join(' ', array_slice($parts, 0, -($sliceLength)));
						//TODO make reverse slice
					} else return false;
				}
			}
		}
	}
	
	protected function getRequestPhone(){
		if(isset($this->request['phone'])){
			return trim($this->request['phone']) ? trim($this->request['phone']) : '';
		}
	}
	
	protected function getRequestRawString(){return $this->request['raw_string'];}
	protected function hasRequestRawString(){return isset($this->request['raw_string']) && $this->request['raw_string'] ? true : false;}
	
	protected function getRequestStreet(){return $this->request['street'];}
	protected function hasRequestStreet(){return isset($this->request['street']) && $this->request['street'] ? true : false;}
	
	protected function getRequestCity(){return $this->request['city'];}
	protected function hasRequestCity(){return isset($this->request['city']) && $this->request['city'] ? true : false;}

	protected function getRequestState(){return $this->request['state'];}
	protected function hasRequestState(){return isset($this->request['state']) && $this->request['state'] ? true : false;}
	
	protected function getRequestZip(){return $this->hasRequestZip() ? $this->request['zip'] : null;}
	protected function hasRequestZip(){return isset($this->request['zip']) && $this->request['zip'] ? true : false;}
	
	protected function getRequestAddress(){
		if($this->hasRequestRawString()){
			return array_shift(explode('|', $this->getRequestRawString()));
		}
	}	

	/*
	protected function getRequestAddress(){
		$address = '';
		if($this->hasRequestStreet())	$address .= ' ' . $this->getRequestStreet() . ',';
		if($this->hasRequestCity())		$address .= ' ' . $this->getRequestCity() . ',';
		if($this->hasRequestState())	$address .= ' ' . $this->getRequestState();
		if($this->hasRequestZip())		$address .= ' ' . $this->getRequestZip();
		return trim($address);
	}
	*/
	protected function genRequestUrl(){}
	protected function getRequestUrl(){
		if($this->genRequestTitle() !== false){
			return $this->genRequestUrl();
		} return false;
	}
	
	// RAW 
	protected function hasRawLinksResponse(){return $this->getRawLinksResponse() ? true : false;}
	protected function getRawLinksResponse(){return $this->rawLinksHtml;}
	protected function setRawLinksResponse($response){return $this->rawLinksHtml = $response;}
	protected function createRawLinksResponse($url){$this->setRawLinksResponse($this->makeRequest($url));}
	
	protected function hasRawLinksObject(){return $this->getRawLinksObject() ? true : false;}
	protected function getRawLinksObject(){return $this->rawLinksDom;}
	protected function setRawLinksObject($object){$this->rawLinksDom = $object;}
	protected function createRawLinksObject(){$this->setRawLinksObject(new simple_html_dom($this->getRawLinksResponse()));}

	protected function hasRawLinkObject(){return $this->getRawLinkObject() ? true : false;}
	protected function getRawLinkObject(){return $this->rawLinkDom;}
	
	// ITEM
	protected function hasHtmlResponse(){return $this->getHtmlResponse() ? true : false;}
	protected function getHtmlResponse(){return $this->html_response;}
	protected function setHtmlResponse($response){return $this->html_response = $response;}
	protected function createHtmlResponse($url){$this->setHtmlResponse($this->makeRequest($url));}
	
	protected function hasHtmlObject(){return $this->getHtmlObject() ? true : false;}
	protected function getHtmlObject(){return $this->html_response_object;}
	protected function setHtmlObject($object){$this->html_response_object = $object;}
	protected function createHtmlObject(){$this->setHtmlObject(new simple_html_dom($this->getHtmlResponse()));}
	
	protected function parseDataObject(){}
	protected function hasDataObject(){return $this->company ? true : false;}
	protected function getDataObject(){return $this->company;}
	protected function setDataObject($object){$this->company = $object;}
	protected function createDataObject(){
		if($this->hasHtmlObject()){
			$this->setDataObject($this->parseDataObject());
		}
	}
	
	protected function destroy(){
		$this->destroyDataObject();
		$this->destroyHtmlObject();
		$this->destroyHtmlResponse();
	}
	
	protected function destroyHtmlResponse(){$this->html_response = null;}
	protected function destroyHtmlObject(){
		if($this->hasHtmlObject()){
			if($this->getHtmlObject() instanceof simple_html_dom_node || $this->getHtmlObject() instanceof simple_html_dom){
				$this->getHtmlObject()->clear();
			}
		} $this->setHtmlObject(null);
	}
	
	protected function destroyDataObject(){
		if($this->hasDataObject()){
			if($this->getDataObject() instanceof simple_html_dom_node || $this->getDataObject() instanceof simple_html_dom){
				$this->getDataObject()->clear();
			}
		} $this->setDataObject(null);
	}
	
	// ------------ DESTROY RAW
	protected function destroyRaw(){
		$this->destroyRawLinkObject();
		$this->destroyRawLinksObject();
		$this->destroyRawLinksResponse();
	}
	
	protected function destroyRawLinkObject(){
		if($this->rawLinkDom){
			if($this->rawLinkDom instanceof simple_html_dom_node || $this->rawLinkDom instanceof simple_html_dom){
				$this->rawLinkDom->clear();
			}
		} $this->rawLinkDom = null;
	}

	protected function destroyRawLinksObject(){
		if($this->hasRawLinksObject()){
			if($this->getRawLinksObject() instanceof simple_html_dom_node || $this->getRawLinksObject() instanceof simple_html_dom){
				$this->getRawLinksObject()->clear();
			}
		} $this->setRawLinksObject(null);
	}
	
	protected function destroyRawLinksResponse(){
		$this->rawLinksHtml = null;
	}
	//-------------END
	
	public function isServiceEnabled(){return $this->ServiceEnabled ? true : false;}
	
    public static function makeRequest($url, $loop = 1){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.6 Safari/537.11');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $result = curl_exec($ch);
		if(!$result && $loop < 2){
			$loop++;
			$result = self::makeRequest($url, $loop);
		}
		curl_close($ch);
		return $result;
    }
	
	public static function getHtml($url, $return = 'content', $data = array(), $type = 'html', $header = array()){
		$res = array();
		$options = array(
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // do not return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_USERAGENT      => 'Google', // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 60,      // timeout on connect
			CURLOPT_TIMEOUT        => 60,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects\
		);
		if($data){
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_VERBOSE] = 1;
			if($type == 'json'){
				$data = json_encode($data);
				$header = array_merge($header, array('Content-Type: application/json;charset=UTF-8'));
			}
			$options[CURLOPT_POSTFIELDS] = $data;
		}
		$options[CURLOPT_HTTPHEADER] = $header;
		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		//$err     = curl_errno($ch);
		//$errmsg  = curl_error($ch);
		$header  = curl_getinfo($ch);
		curl_close($ch);
		if($header['http_code'] != 400){
			$res['content'] = $content;
			$res['url'] = $header['url'];
			return $res[$return];
		} else return false;
	}
	
	protected function clearData(){$this->data = array();}
	public function results(){return $this->data;}

	public function getSearchStatus(){return $this->ServiceStatus;}
	protected function setSearchStatusTrue(){$this->ServiceStatus = self::SERVICE_SEARCH_STATUS_TRUE;}
	protected function setSearchStatusFalse(){$this->ServiceStatus = self::SERVICE_SEARCH_STATUS_FALSE;}
	protected function setSearchStatusError($message){
		$this->ServiceStatusErrorMessage = $message;
		$this->ServiceStatus = self::SERVICE_SEARCH_STATUS_ERROR;
	}
	
	protected function getRequestFieldsCount(){
		$count = 0;
		foreach($this->request as $v){
			$v = trim($v);
			if(!empty($v)){
				$count++;
			}
		} return $count;
	}
	
	protected static function beforeCompareCleaning($field, $value){
		if($field == 'phone'){
			return preg_replace('/[^\d]/', '', $value);
		} return $value;
	}
	
	protected function save($empty = false){
		if($empty) $this->clearData();
		$this->saveToCache($this->data);
	}
	
	protected function isCacheExpired($path){
		if((time()-filemtime($path)) < $this->cacheLifetime){
			return false;
		} return true;
	}
	
	protected function saveToCache($data){
		$servicepath = $this->getCachePath();
		$fh = fopen($servicepath, 'w+');
		fwrite($fh, serialize($data));
		fclose($fh);
	}
	
	protected function getFromCache(){
		$servicepath = $this->getCachePath();
		if(file_exists($servicepath) && is_file($servicepath)){
			return unserialize(file_get_contents($servicepath));
		} return null;
	}
	
	protected function getRequestPath(){
		$path = $this->getServicePath();
		$itemhash = md5(trim($this->request['title']));
		return $path . $itemhash;
	}
	
	protected function getCachePath(){
		return $this->getRequestPath();
	}
	
	protected function getServicePath(){
		$path = BASE_DIR . 'cache/' . $this->ServiceCode . '/';
		if(!file_exists($path)){
			mkdir($path);
			chmod($path, 0777);
		} return $path;
	}
	
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
		return preg_replace(array('/\r\n/u', '/\s+|&nbsp;/u'), array(' ', ' '), trim($v));
	}

	protected function createBasicData($data = array()){
		$basic = '';
		if(isset($data['title']))	$basic .= $data['title'].'<br/>';
		if(isset($data['street']))	$basic .= ' '.$data['street'].',';
		if(isset($data['city']))	$basic .= ' '.$data['city'].',';
		if(isset($data['state']))	$basic .= ' '.$data['state'];
		if(isset($data['zip']))		$basic .= ' '.$data['zip'];
		if(isset($data['phone']))	$basic .= '<br/>'.$data['phone'];
		return trim($basic);
	}
	
	protected $fields = array('basic', 'category', 'website', 'description', 'photo', 'offer');
	
	protected function getJsonData(){
		$count = 0;
		$data = array();
		if($this->ServiceStatus == 1) $data['url'] = $this->data['url'];
		foreach($this->fields as $key){
			if($this->ServiceStatus == 1){
				if($key == 'basic'){
					$data[$key] = $this->createBasicData($this->data[$key]);
				} else $data[$key] = (isset($this->data[$key]) && !empty($this->data[$key])) ? true : false;
			} else $data[$key] = false;
			if($data[$key]) $count++;
		}
		if($count == 0){
			$data['status'] = 'fail';
		}elseif($count < 3){
			$data['status'] = 'alert';
		}elseif($count >= 3){
			$data['status'] = 'listed';
		} return $data;
	}
	
	public function renderJSON(){
		$data = $this->getJsonData();
		echo json_encode(array(
			'status' => $this->ServiceStatus, 
			'message' => $this->ServiceStatusErrorMessage, 
			'data' => $data
		));
		exit;
	}
	
}
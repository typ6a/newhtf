<?php

KDGLoader::loadEntityClass('Developer');
KDGLoader::loadModelClass('DeveloperModel');

KDGLoader::loadEntityClass('Publisher');
KDGLoader::loadModelClass('PublisherModel');

KDGLoader::loadEntityClass('Content');
KDGLoader::loadModelClass('ContentModel');

KDGLoader::loadEntityClass('Genre');
KDGLoader::loadModelClass('GenreModel');

KDGLoader::loadEntityClass('ContentToGenre');
KDGLoader::loadModelClass('ContentToGenreModel');

KDGLoader::loadEntityClass('Feature');
KDGLoader::loadModelClass('FeatureModel');

KDGLoader::loadEntityClass('ContentToFeature');
KDGLoader::loadModelClass('ContentToFeatureModel');

KDGLoader::loadModelClass('CategoryModel');
KDGLoader::loadModelClass('ContentToCategoryModel');

class XboxOneDLCContentDetailsPageParser extends KDGParser {

	protected $RawItem	= null;
	protected $content_object	= null;
	
	public function __construct($response, $content_object){
        $this->content_object = $content_object;
        parent::__construct($response);
	}
	
	protected function hasDataObject(){
        return true;
	}
    
    protected function parseValue($val, $pattern){
        if(preg_match($pattern, $val, $m)){
            if(isset($m[1])){
                return $m[1];
            }
        } return null;
    }
    
	protected function parseDescription(){
        $res = $this->getResponseObject()->query('//*[@id="gameDetailsSection"]/div/ul[@class="description"]')->item(0);
        if($res){
            $el = $this->nodeToXPath($res)->query('//span')->item(0);
            if($el){
                return $el->nodeValue;
            }
        }
    }
    
    protected function parseFacebookIframeUrl(){
        $iframe = $this->getResponseObject()->query('//*[@id="ProductTitleZone"]/iframe')->item(0);
        if($iframe){
            return $iframe->getAttribute('src');
        }
    }
    
	protected function parsePrice(){
        $res = $this->getResponseObject()->query('//*[@id="purchaseInfo"]/div/h1')->item(0);
        if($res){
            return str_replace('$', '', $res->nodeValue);
        } return null;
    }
    
    protected function parseSize($val){
        if(stristr($val, 'kb')){
            $multiplier = 1024;
        }elseif(stristr($val, 'mb')){
            $multiplier = 1024*1024;
        }elseif(stristr($val, 'gb')){
            $multiplier = 1024*1024*1024;
        }else{
            $multiplier = 1;
        }
        return round(floatval($val) * $multiplier);
    }
    
	protected function parse(){
        $data = array();
        $details = $this->getResponseObject()->query('//*[@id="gameDetailsSection"]/div/ul[@class="fields"]')->item(0);
        if($details){
            $items = $this->nodeToXPath($details)->query('//li');
            if($items->length > 0){
                foreach($items as $bundle_item){
                    $val = $this->cleanData($bundle_item->nodeValue);
                    //pre($val);
                    //continue;
                    if(stristr($val, 'release')){
                        $date = $this->parseValue($val, '/^.+:\040?(.+)$/');
                        $data['release_date'] = date('Y-m-d H:i:s', strtotime($date));
                    }elseif(stristr($val, 'developer')){
                        $data['developer'] = $this->parseValue($val, '/^.+:\040?(.+)$/');
                    }elseif(stristr($val, 'publisher')){
                        $data['publisher'] = $this->parseValue($val, '/^.+:\040?(.+)$/');
                    }elseif(stristr($val, 'genre')){
                        $genres = $this->parseValue($val, '/^.+:\040?(.+)$/');
                        if($genres){
                            $data['genres'] = explode(',', $genres);
                        }
                    }elseif(stristr($val, 'size')){
                        $val = $this->parseValue($val, '/^.+:\040?(.+)$/');
                        $data['size'] = $this->parseSize($val);
                    }
                }
            }

            $data['description'] = $this->parseDescription();
            $data['price'] = $this->parsePrice();
            //$data['facebook_iframe_url'] = $this->parseFacebookIframeUrl();
            
            //pre($data,1);

            if(isset($data['developer'])){
                $dobject = DeveloperModel::findOneByTitle($data['developer']);
                if(!$dobject){
                    $dobject = new Developer();
                    $dobject->fromArray(array(
                        'title' => $data['developer']
                    ));
                    $developer_id = $dobject->save();
                } else {
                    $developer_id = $dobject->id;
                }
                $data['developer_id'] = $developer_id;
            }

            if(isset($data['publisher'])){
                $dobject = PublisherModel::findOneByTitle($data['publisher']);
                if(!$dobject){
                    $dobject = new Publisher();
                    $dobject->fromArray(array(
                        'title' => $data['publisher']
                    ));
                    $publisher_id = $dobject->save();
                } else {
                    $publisher_id = $dobject->id;
                }
                $data['publisher_id'] = $publisher_id;
            }

            $data = $this->cleanData($data);

            pre($data,1);
            
            $data['processed'] = 1;
			
            $this->content_object->fromArray($data);
            $this->content_object->save();
            
            if(isset($data['genres'])){
                // remove old genres associations
                $res = ContentToGenreModel::deleteByContentId($this->content_object->id);
                if($res){
                    foreach($data['genres'] as $genre){
                        $gobject = GenreModel::findOneByTitle($genre);
                        if(!$gobject){
                            $gobject = new Genre();
                            $gobject->fromArray(array(
                                'title' => $genre
                            ));
                            $genre_id = $gobject->save();
                        } else {
                            $genre_id = $gobject->id;
                        }

                        $ctg = new ContentToGenre();
                        $ctg->fromArray(array(
                            'content_id' => $this->content_object->id,
                            'genre_id' => $genre_id
                        ));
                        $ctg->save();
                    }
                }
            }

        }
	}

}
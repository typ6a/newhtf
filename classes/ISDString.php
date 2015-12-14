<?php

class ISDString {

	public static function truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false) {
	    if($break_words){
    		$wordsCount = str_word_count($string);
			if($wordsCount > $length){
				$words = split(' ', $string, $length+1);
				return join(' ', array_slice($words, 0, $length)) . ' ' . $etc;
			} else return $string;
		}
		if ($length == 0) return '';
	    if (strlen($string) > $length) {
			$length -= strlen($etc);
			if (!$break_words && !$middle) $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
			if(!$middle) return substr($string, 0, $length).$etc;
			else return substr($string, 0, $length/2) . $etc . substr($string, -$length/2);
		} else return $string;
	}
	
	public static function slug($string, $replacement = '-') {
		$map = array(
			'/à|á|å|â/' => 'a',
			'/è|é|ê|ẽ|ë/' => 'e',
			'/ì|í|î/' => 'i',
			'/ò|ó|ô|ø/' => 'o',
			'/ù|ú|ů|û/' => 'u',
			'/ç/' => 'c',
			'/ñ/' => 'n',
			'/ä|æ/' => 'ae',
			//'/ö/' => 'oe',
			//'/ü/' => 'ue',
			'/Ä/' => 'Ae',
			//'/Ü/' => 'Ue',
			//'/Ö/' => 'Oe',
			'/ß/' => 'ss',
			// Turkish START ---
			'/İ|ı/' => 'i',
			'/Ö|ö/' => 'o',
			'/Ü|ü/' => 'u',
			'/Ç|ç/' => 'c',
			'/Ğ|ğ/' => 'g',
			'/Ş|ş/' => 's',
			// Turkish END ---
			'/[^\w\s]/' => ' ',
			'/\\s+/' => $replacement,
			//self::insert('/^[:replacement]+|[:replacement]+$/', array('replacement' => preg_quote($replacement, '/'))) => '',
		);
		return mb_strtolower(preg_replace(array_keys($map), array_values($map), $string), 'UTF-8');
	}

	public static function tableize($string) {
		return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $string));
    }
	
	public static function insert($str, $data, $options = array()) {
		$defaults = array('before' => ':', 'after' => null, 'escape' => '\\', 'format' => null, 'clean' => false);
		$options += $defaults;
		$format = $options['format'];
		if (!isset($format)) {
			$format = sprintf(
				'/(?<!%s)%s%%s%s/',
				preg_quote($options['escape'], '/'),
				str_replace('%', '%%', preg_quote($options['before'], '/')),
				str_replace('%', '%%', preg_quote($options['after'], '/'))
			);
		}
		if (!is_array($data)) {$data = array($data);}
		if (array_keys($data) === array_keys(array_values($data))) {
			$offset = 0;
			while (($pos = strpos($str, '?', $offset)) !== false) {
				$val = array_shift($data);
				$offset = $pos + strlen($val);
				$str = substr_replace($str, $val, $pos, 1);
			}
		} else {
			asort($data);
			$hashKeys = array_map('md5', array_keys($data));
			$tempData = array_combine(array_keys($data), array_values($hashKeys));
			foreach ($tempData as $key => $hashVal) {
				$key = sprintf($format, preg_quote($key, '/'));
				$str = preg_replace($key, $hashVal, $str);
			}
			$dataReplacements = array_combine($hashKeys, array_values($data));
			foreach ($dataReplacements as $tmpHash => $data) {
				$str = str_replace($tmpHash, $data, $str);
			}
		}
		if (!isset($options['format']) && isset($options['before'])) {
			$str = str_replace($options['escape'].$options['before'], $options['before'], $str);
		}
		return $str;
	}

	public function underscore($camelCasedWord) {
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
	}

	public static function stripInvalidChars($string, $name = null, $disassemble = false) {
		if($disassemble) self::disassembleString($string);
		return preg_replace('/[^(\x20-\x7F\xA1-\xBF\xC380-\xC5BE)]*/', '', $string);
		//\xC387\xC396\xC39C\xC3A7\xC3B6\xC3BC\xC4B0\xC4B1\xC49E\xC49F\xC59E\xC59F
	}

	public static function disassembleString($string, $stop = true){
		$i = 0;
		$count = mb_strlen($string, 'UTF-8');
		//p('Htmlentities: ' . htmlentities($string, null, 'UTF-8'));
		//p('Htmlspecialchars: ' . htmlspecialchars($string, null, 'UTF-8'));
		$encoding = mb_detect_encoding($string, 'auto');
		//p('Strlen: ' . $count);
		//p('Encoding: ' . $encoding);
		while($i < $count){
			$char = mb_substr($string, $i, 1, 'UTF-8');
			$dec = ord($char);
			$hex = dechex($dec);
			echo $char . ' ->' . $dec . ' -> ' . $hex . '<br/>';
			$i++;
		} if($stop) exit('finished');
	}

	public static function cleanIllegalCharacters($value) {
		$value = ISDString::stripInvalidChars($value);
		$value = @iconv('UTF-8//IGNORE', 'UTF-8//IGNORE', $value);
		return $value;
	}
	
	public static function getValidSpaseFormat($bytes) {
    	$kb = 1024;
    	$mb = $kb*1024;
    	$gb = $mb*1024;
    	if ($bytes >= $gb){
    		$str = ceil($bytes/$gb*100)/100;
    		$str .= ' GB';
    	}elseif ($bytes >= $mb){
    		$str = ceil($bytes/$mb*100)/100;
    		$str .= ' MB';
    	}elseif ($bytes >= $kb){
    		$str = ceil($bytes/$kb*100)/100;
    		$str .= ' KB';
    	}elseif($bytes < $kb){
    		$str = '1 Kb';
    	} return $str;
    }

	public static function getTurkishHex(){
		$map = array(
			'/&#304;/' => 'İ',
			'/&#305;/' => 'ı',
			'/&Ouml;|&#214;/' => 'Ö',
			'/&ouml;|&#246;/' => 'ö',
			'/&Uuml;|&#220;/' => 'Ü',
			'/&uuml;|&#252;/' => 'ü',
			'/&Ccedil;|&#199;/' => 'Ç',
			'/&ccedil;|&#231;/' => 'ç',
			'/&#286;/' => 'Ğ',
			'/&#287;/' => 'ğ',
			'/&#350;/' => 'Ş',
			'/&#351;/' => 'ş',
			'/&#8356;/' => '₤',
		);
		$res = array();
		foreach($map as $char){
			$res[$char] = dechex(ord($char));
		}
		return $res;
	}

	public static function convertHtmlCodesToChars($value){
		$map = array(
			'/&#304;/' => 'İ',
			'/&#305;/' => 'ı',
			'/&Ouml;|&#214;/' => 'Ö',
			'/&ouml;|&#246;/' => 'ö',
			'/&Uuml;|&#220;/' => 'Ü',
			'/&uuml;|&#252;/' => 'ü',
			'/&Ccedil;|&#199;/' => 'Ç',
			'/&ccedil;|&#231;/' => 'ç',
			'/&#286;/' => 'Ğ',
			'/&#287;/' => 'ğ',
			'/&#350;/' => 'Ş',
			'/&#351;/' => 'ş',
			'/&#8356;/' => '₤',
			'/&rsquo;|&#39;/' => '\'',
		);
		return preg_replace(array_keys($map), array_values($map), $value);
	}
}
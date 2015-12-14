<?php 

class KDGLoader {

	const IMG_PATH = 'images/';
	const CLASS_PATH = 'classes/';
	const JS_PATH = 'js/';
	const CSS_PATH = 'css/'; 

	public static function object_to_array($object) {return (is_object($object)) ? get_object_vars($object) : $object;}

	public static function render($partial, $parameters = array()){return self::view($partial, $parameters, true);}
	public static function view($view, $vars = array(), $return = FALSE) {return self::load(array('_ci_view' => $view, '_ci_vars' => self::object_to_array($vars), '_ci_return' => $return));}

	public static function getScriptName(){return basename($_SERVER['SCRIPT_NAME']);} 
	
	public static function genUrl($path = ''){return self::getBaseUrl() . $path;}
	public static function genSuiteUrl($path = ''){return self::getBaseSuiteUrl() . $path;}
	
	public static function getBaseUrl(){return rtrim(BASE_URL, '/') . '/';}
	public static function getBaseSuiteUrl(){return BASE_SUITE_URL;}
	
	public static function getImgBaseUrl(){return rtrim(BASE_URL, '/') . '/' . self::IMG_PATH;}
	public static function genImgUrl($uri = ''){return self::getImgBaseUrl() . $uri;}
	public static function getImgBasePath(){return BASE_DIR . self::IMG_PATH;}
	public static function genImgPath($path = ''){return self::getImgBasePath() . $path;}
	
	public static function getBasePath(){return BASE_DIR;}
	public static function genBasePath($path = ''){return self::getBasePath() . $path;}

	public static function genJavascriptBaseUrl($path = ''){return self::genUrl(self::JS_PATH) . $path;}
	public static function genCssBaseUrl($path = ''){return self::genUrl(self::CSS_PATH) . $path;}
		
	public static function getLibraryPath(){
		return BASE_DIR . self::CLASS_PATH;
	}
	
	public static function loadLibraryClass($path){
		$path = BASE_DIR . self::CLASS_PATH . $path . EXT;
		if(file_exists($path) && is_file($path)){
			require_once $path;
		} else die('Unable to load the Library file: ' . $path);
	}
	
	public static function loadModelClass($path){
		$path = BASE_DIR . self::CLASS_PATH . 'model/' . $path . EXT;
		if(file_exists($path) && is_file($path)){
			require_once $path;
		} else die('Unable to load the Model file: ' . $path);
	}
	
	public static function loadEntityClass($path){
		$path = BASE_DIR . self::CLASS_PATH . 'entity/' . $path . EXT;
		if(file_exists($path) && is_file($path)){
			require_once $path;
		} else die('Unable to load the requested file: ' . $path);
	}
	
	public static function isConsoleRequest(){
		return in_array(PHP_SAPI, array('cgi-fcgi', 'cli')) ? true : false;
	}

	/**
	 * Loader
	 *
	 * This function is used to load views and files.
	 * Variables are prefixed with _ci_ to avoid symbol collision with
	 * variables made available to view files
	 *
	 * @access	private
	 * @param	array
	 * @return	void
	 */
	public static function load($_ci_data) {
		// Set the default data variables
		foreach (array('_ci_view', '_ci_vars', '_ci_path', '_ci_return') as $_ci_val) 
			$$_ci_val = ( ! isset($_ci_data[$_ci_val])) ? FALSE : $_ci_data[$_ci_val];
		// Set the path to the requested file
		if ($_ci_path == '') {
			$_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
			$_ci_file = ($_ci_ext == '') ? $_ci_view.EXT : $_ci_view;
			if (file_exists(VIEW_PATH.$_ci_file)) $_ci_path = VIEW_PATH.$_ci_file;
		} else {
			$_ci_x = explode('/', $_ci_path);
			$_ci_file = end($_ci_x);
		}
		if (!file_exists($_ci_path)) echo('Unable to load the requested file: '.$_ci_file) . '<br/>';
		extract($_ci_vars);	
		ob_start();	
		if ((bool) @ini_get('short_open_tag') === FALSE) {
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		} else include($_ci_path); // include() vs include_once() allows for multiple views with the same name
		//log_message('debug', 'File loaded: '.$_ci_path);
		// Return the file data if requested
		if ($_ci_return === TRUE) {$buffer = ob_get_contents(); @ob_end_clean(); return $buffer;}
		/*
		 * Flush the buffer... or buff the flusher?
		 *
		 * In order to permit views to be nested within
		 * other views, we need to flush the content back out whenever
		 * we are beyond the first level of output buffering so that
		 * it can be seen and included properly by the first included
		 * template and any subsequent ones. Oy!
		 *
		 */	
		if(ob_get_level() > ob_get_level() + 1){
			ob_end_flush();
		}else{
			// PHP 4 requires that we use a global
			global $OUT;
			$OUT->append_output(ob_get_contents());
			@ob_end_clean();
		}
	}
}
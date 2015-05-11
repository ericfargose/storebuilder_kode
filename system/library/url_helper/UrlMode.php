<?php

class UrlModeFactory {
    
	public static $DOMAIN = DOMAIN;
	
	public static $ALL_DOMAINS = array();
	
	
    public static function findMode($server) { 

        $http_host = $server['HTTP_HOST'];        
        $mode = '';        
        $host_arr = explode(".", $http_host);
        $parts = explode('/', $server['REQUEST_URI']);
        list($first) = explode('?', $parts[1]);		
        self::$ALL_DOMAINS = array_merge(self::$ALL_DOMAINS, isset($server['top_level_domains']) ? $server['top_level_domains'] : array(self::$DOMAIN));
        if($host_arr[0] == 'www') { //can be subdirectory or own domain
            array_shift($host_arr);
            // if the host == DOMAIN && first part of request uri is something that then its tld
            if(self::in_domains(implode(".",$host_arr)) && self::match_first($first)) {
                $mode = 'tld';
            }
            // other wise its subdir 
            else if (self::in_domains(implode(".",$host_arr))) {            	
                $mode = 'subdir';
            } else {
				$mode = 'tld';
			}
        } 
        
        //no www
        else {            
            // tld
            if(self::in_domains($http_host) && self::match_first($first)) { 
                $mode = 'tld'; // with the same DOMAIN which means the mall and mall admin case.
            }
            // subdir
            else if (self::in_domains($http_host)) { 
                $mode = 'subdir';                
            }
            //is numeric
            else if(is_numeric($host_arr[0])) {
                $mode = 'ipaddress';
            }            
            //two possibilites - tld or subdomain
            else {
                array_shift($host_arr); //remove the subdomain part
                //if it matches with the specified domain, then its definitely subdirectory
                if(self::in_domains(implode(".",$host_arr)) && self::match_first($first)) {
                    $mode = 'subdomain';
                } else if (self::in_domains(implode(".",$host_arr)) && !self::match_first($first)) {
                	$mode = 'subdir';                 	
                } else {
                    // some other domain
                    $mode = 'tld';
                }
            }			
        }        
		// echo $mode; exit;
        return $mode;        
    }
    
    public static function All_DOMAINS($registry){
    	$query = $registry->get('db')->query("SELECT domain FROM " . DB_PREFIX . "sb_xstore_meta WHERE domain <> '' group by domain");

    	$domain_arr = array();
    	foreach($query->rows as $re){
    		$domain_arr[] = 'www.'.$re['domain'];
    		$domain_arr[] = $re['domain'];
    	}
    	$a= array(
    		$domain_arr[] = 'www.'.DOMAIN,
    		$domain_arr[] = DOMAIN
    	);

    	return isset($domain_arr) ? $domain_arr : $a;
    	//return $domain_arr;
    }
    
    public static function in_domains($domain) {
    	return in_array($domain, self::$ALL_DOMAINS);
    }
    
    private static function match_first($first) {
    	return in_array($first, array('admin', 'index.php', '/', '', 'find'));
    }
    
    public static function getInstance($registry, $server) {
    	
    	$result = self::All_DOMAINS($registry);
    	$server['top_level_domains']= $result;
        $mode = self::findMode($server);        
        switch ($mode) {            
        case "subdomain":
            $instance = new UrlModeSubDomain($registry, $server);			
            break;
        case "subdir":
            $instance = new UrlModeSubDirectory($registry, $server);				
            break;            
        case "tld":
            $instance = new UrlModeTopLevelDomain($registry, $server);				
            break;            
        case "ipaddress":
            $instance = new UrlModeIpAddress($registry, $server);            
        }        
        $instance->setMode($mode);
        
        return $instance;        
    }

    /**
     * Method to validate the url segment which means the part of the 
     * url that is specified by the seller while creating the store
     * or saving the settings. 
     * for eg. in http://eric.kodemall.com, "eric" is the url segment
     * it needs to be validated against directory names and for aplhanumetic characters 
     * along with underscores
     * @param String $segment
     * @param String $mode (subdomain|subdir|tld)
     * @return Boolean is valid or not
     */
    public static function validateUrlSegment($segment, $mode) {
        //echo $mode;exit;
        if ('tld' === $mode) {
            $store_url_explode = explode('.', $segment);
            if(count($store_url_explode) == 2){
                $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9]{2,20}$/';
            } elseif(count($store_url_explode) == 3){
                $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]$/';
            } else {
                $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]{1,20}$/';    
            }
            //$re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-]+\.[a-zA-Z0-9-._]+[^.]+[a-zA-Z0-9]{0,20}$/';
        } else {
            //$re = '/^[a-zA-Z0-9]+([\_\-]?[a-zA-Z0-9]+)?$/';
            $re = '/^[a-zA-Z0-9]+[a-zA-Z0-9-._]+[a-zA-Z0-9]$/';
        }
        if (!preg_match($re, $segment)) {
            return false;
        }
        $basedir = DIR_APPLICATION . '../';
        $pathnames = scandir($basedir);
        foreach ($pathnames as $pathname) {
            if (is_dir($basedir.$pathname) && $segment === $pathname) {
                return false;
            }
        }
        return true;
    }

    public static function isValidURL($url) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }    
}

interface UrlMode {
    
    public function loadFromServerEnv();
    
    public function loadFromStoreConfig();
    
    public function loadFromCustomConfig($custom);
    
}

abstract class UrlModeAbstract implements UrlMode {
    
    protected $registry;

    /**
     * Array carrying the HTTP_HOST and REQUEST_URI values of 
     * These may be the actual values from the $_SERVER superglobal
     * or they may also be pseudo values passed to simulate a url
     */
    protected $server;
    
    protected $http_server;
    
    protected $https_server;
    
    protected $http_image;
    
    protected $https_image;
    
    protected $store_url;
    
    protected $html_meta_base;	
    
    protected $mode;
    
    protected $can_be_www = false;
    
    protected $mall_base_url;
    
    public $is_admin = false;

    public static $INSTALLATION_DIR = 'root';
    
    //while loading a store, urls from db will be matched with one of these urls
    protected $match_urls = array();
    
    public function __construct($registry, $server) {		
        $this->registry = $registry;
        $this->server = $server;
        $this->loadFromServerEnv();        
    }
    
    public function getHttpServer() {       	
        return $this->http_server;
    }
    
    public function getHttpsServer() {        
        return $this->https_server;	
    }
    
    public function getHttpImage() {
        return $this->http_image;
    }
    
    public function getHttpsImage() {        
        return $this->https_image;
    }
    
    public function getStoreUrl() {
        return $this->store_url;
    }
    
    public function getHtmlMetaBase() {
        return $this->html_meta_base;	
    }
    
    /**
     * This method is irrelevant in case of store builder projects
     */
    public function getMallBaseUrl() {
    	return $this->getHttpServer() . 'mall/';
    }
    
    public function setMode($mode) {
        $this->mode = $mode;
    }
    
    public function getMode() {
        return $this->mode;
    }
    
    public function getMatchUrls() {   
    	if(preg_match('/admin$/',$this->store_url) || preg_match('/admin\/$/',$this->store_url)) {    		
            $url_exp = explode("/", $this->store_url);
            $last = array_pop($url_exp);
            if(!$last) {
                $last = array_pop($url_exp);
            }
            $this->store_url = implode("/", $url_exp) . '/';
    	}     
        $this->match_urls[] = $this->store_url;        
        if(!$this->can_be_www) {
            return $this->match_urls;
        }        
        //if url can have www and if its not present in the store_url, 
        //then add one more candidate for matching which will have wwww in it
        if(!UrlModeAbstract::containsWWW($this->store_url)) {
            $this->match_urls[] = preg_replace('/^http:\/\//', "http://www.", $this->store_url);
        } 
        //else if it is present, add a url without www
        else {
            $this->match_urls[] = preg_replace('/^http:\/\/www./', "http://", $this->store_url);
        }        
        return $this->match_urls;        
    }
    
    public function loadFromStoreConfig() {    	
    	$this->http_server = $this->registry->get('config')->get('config_url');
    	$this->https_server = UrlModeAbstract::makeHttps($this->http_server,$this->registry->get('config')->get('config_secure'));
    	$this->html_meta_base = $this->https_server;
    }
    
    public function loadFromCustomConfig($custom) {
    	$this->http_server = $custom['config_url'];
    	$this->https_server = UrlModeAbstract::makeHttps($this->http_server,$custom['config_secure']); 
    }

    /**
     * Depending upon the $config_ssl, make the change the 
     * url from http:// to https://
     * @param $url
     * @param $config_ssl
     */
    public static function makeHttps($url, $config_ssl) {    	
        if ($config_ssl) {
            $url = 'https://' . substr($url, 7);						
        } 
        return $url;
    }

    /**
     * To check if the url contains www
     * @param String $url to be checked.
     * @return boolean 
     */
    public static function containsWWW($url) {
        return preg_match('/^http:\/\/www./', $url);
    }

    /**
     * Method to find the name of the directory in which the kodemall installation
     * has been installed.
     * @staticMethod
     * @sideEffect: update the value of $INSTALLATION_DIR class property in case 
     *              constant named SUBDIR_BASE is defined
     * @return String $installation_dir
     */
    public static function getInstallationDir() {
        $dir = '';
        if (defined('SUBDIR_BASE')) {
            self::$INSTALLATION_DIR = SUBDIR_BASE;
        }
        return self::$INSTALLATION_DIR;
    }

    /**
     * Helper method to find if the kodemall installation has been done in the 
     * web server root dir.
     * @staticMethod
     * @return Boolean
     */
    public static function isRootInstalled() {
        $installation_dir = self::getInstallationDir();
        return ($installation_dir === 'root');
    }
}

class UrlModeSubDomain extends UrlModeAbstract {
    
    public function loadFromServerEnv() {
    	$this->store_url = 'http://' . $this->server['HTTP_HOST'] .'/';
        if (!UrlModeAbstract::isRootInstalled()) {
            $this->store_url .= UrlModeAbstract::getInstallationDir() . '/';
        }
    	$this->can_be_www = false;        
    }
    
    public function loadFromStoreConfig() {
    	parent::loadFromStoreConfig();
    	$this->http_image = $this->http_server . 'image/';
        $this->https_image = UrlModeAbstract::makeHttps($this->http_image,$this->registry->get('config')->get('config_secure'));     	
    }
    
    public function loadFromCustomConfig($custom) {
    	parent::loadFromCustomConfig($custom);
    	$this->http_image = $this->http_server . 'image/';
        $this->https_image = UrlModeAbstract::makeHttps($this->http_image, $custom['config_secure']);  
        $this->html_meta_base = $this->https_server;	 
    }
}

class UrlModeSubDirectory extends UrlModeAbstract {
    
    public function loadFromServerEnv() {
        if(count(explode('/', $this->server['REQUEST_URI'])) == 2){
            $this->server['REQUEST_URI'] .= '/';
        }        
        $request_uri_arr = explode('/', $this->server['REQUEST_URI']);
        do {
            $i = array_pop($request_uri_arr);
        } while (!preg_match('/^index.php.*/', $i) 
                 && !preg_match('/^admin.*/', $i)
                 && $i != 'find'
                 && $i);
        //handle unknown sub-directory url eg - robots
        if($request_uri_arr){
            // handle the /admin/index.php case- where admin will still remain in the 
            list($last) = array_slice($request_uri_arr, -1);
            if ($last === 'admin') {
                array_pop($request_uri_arr);
            }
        } else {
            $request_uri_arr = explode('/', $this->server['REQUEST_URI']);
        }
        $this->store_url = 'http://' . $this->server['HTTP_HOST'] . implode('/',$request_uri_arr) . '/';
        $this->can_be_www = true;
    }
    
    public function loadFromStoreConfig() {
    	parent::loadFromStoreConfig();
    	$arr = explode("/", $this->http_server);
        //$domain = UrlModeAbstract::containsWWW($this->http_server) ? 'www.'. UrlModeFactory::$DOMAIN : UrlModeFactory::$DOMAIN;
        //$base = !UrlModeAbstract::isRootInstalled() ? UrlModeAbstract::getInstallationDir() : $domain;
    	while (!UrlModeFactory::in_domains($base = array_pop($arr))) { }  // pop array elements until base is found
        $ssl = $this->registry->get('config')->get('config_secure');
        $html_meta_base = implode("/", $arr) . '/'. $base . '/';
    	$this->html_meta_base = UrlModeAbstract::makeHttps($html_meta_base, $ssl);
    	$this->http_image = $html_meta_base . 'image/';
    	$this->https_image = UrlModeAbstract::makeHttps($this->http_image, $ssl);     	
    }
    
    public function loadFromCustomConfig($custom) {
        parent::loadFromCustomConfig($custom);
    	$arr = explode("/", $this->http_server);
    	$domain = UrlModeAbstract::containsWWW($this->http_server) ? 'www.'. UrlModeFactory::$DOMAIN : UrlModeFactory::$DOMAIN;
        $base = !UrlModeAbstract::isRootInstalled() ? UrlModeAbstract::getInstallationDir() : $domain;
    	while (!UrlModeFactory::in_domains($base = array_pop($arr))) { }  // pop array elements until base is found
    	$html_meta_base = implode("/", $arr) . '/'. $base . '/';    	
    	$ssl = $custom['config_secure'];
    	$this->html_meta_base = UrlModeAbstract::makeHttps($html_meta_base, $ssl);        
    	// commented by Vinod $this->http_server . 'image/';
    	$this->http_image = $html_meta_base . 'image/';
    	$this->https_image = UrlModeAbstract::makeHttps($this->http_image, $custom['config_secure']);
    }
}

class UrlModeTopLevelDomain extends UrlModeAbstract {
    
    public function loadFromServerEnv() {
    	$this->store_url = 'http://' . $this->server['HTTP_HOST'] .'/';
    	$m = preg_match('/^http:\/\/[^.]+\.(.+)\//', $this->store_url, $matches);
    	if ($m) {
    		list($url, $domain) = $matches;
    		UrlModeFactory::$DOMAIN = $domain;
    	} 
        if (!UrlModeAbstract::isRootInstalled()) {
            $this->store_url .= UrlModeAbstract::getInstallationDir() . '/';
        }
    	$this->can_be_www = true;
    }
    
    public function loadFromStoreConfig() {
    	parent::loadFromStoreConfig();
    	$this->http_image = $this->http_server . 'image/';
        $this->https_image = UrlModeAbstract::makeHttps($this->http_image, $this->registry->get('config')->get('config_secure'));
        $arr = explode("/", $this->http_server);
        //$domain = UrlModeAbstract::containsWWW($this->http_server) ? 'www.'. UrlModeFactory::$DOMAIN : UrlModeFactory::$DOMAIN;
        //$base = !UrlModeAbstract::isRootInstalled() ? UrlModeAbstract::getInstallationDir() : $domain;
		// print_r($arr); exit;		
       	while (!UrlModeFactory::in_domains($base = array_pop($arr))) { }  // pop array elements until base is found		
    	$html_meta_base = implode("/", $arr) . '/'. $base . '/'; 
    	$ssl = $this->registry->get('config')->get('config_secure');
    	$this->html_meta_base = UrlModeAbstract::makeHttps($html_meta_base, $ssl);       	
    }
    
    public function loadFromCustomConfig($custom) {
    	parent::loadFromCustomConfig($custom);
    	$this->http_image = $this->http_server . 'image/';
        $this->https_image = UrlModeAbstract::makeHttps($this->http_image, $custom['config_secure']);
        $arr = explode("/", $this->http_server);
        //$domain = UrlModeAbstract::containsWWW($this->http_server) ? 'www.'. UrlModeFactory::$DOMAIN : UrlModeFactory::$DOMAIN;
        //$base = !UrlModeAbstract::isRootInstalled() ? UrlModeAbstract::getInstallationDir() : $domain;
    	while (!UrlModeFactory::in_domains($base = array_pop($arr))) { }  // pop array elements until base is found
    	$html_meta_base = implode("/", $arr) . '/'. $base . '/'; 
    	$ssl = $custom['config_secure'];
    	$this->html_meta_base = UrlModeAbstract::makeHttps($html_meta_base, $ssl);        	 
    }    
}

class UrlModeIpAddress extends UrlModeAbstract {
    
    public function loadFromServerEnv() {
    	if(count(explode('/', $this->server['REQUEST_URI'])) == 2){
            $this->server['REQUEST_URI'] .= '/';
        }        
        $request_uri_arr = explode('/', $this->server['REQUEST_URI']);	    
        do {
            $i = array_pop($request_uri_arr);
        } while (!preg_match('/^index.php.*/', $i) 
                 && !preg_match('/^admin.*/', $i)
                 && $i != 'find'
                 && $i);
        //handle unknown sub-directory url eg - robots
        if($request_uri_arr){
            // handle the /admin/index.php case- where admin will still remain in the 
            list($last) = array_slice($request_uri_arr, -1);
            if ($last === 'admin') {
                array_pop($request_uri_arr);
            }
        } else {
            $request_uri_arr = explode('/', $this->server['REQUEST_URI']);
        }
        $this->store_url = 'http://' . $this->server['HTTP_HOST'] . implode('/',$request_uri_arr) . '/';        
        $this->can_be_www = false; 
    }
    
    public function loadFromStoreConfig() {
    	parent::loadFromStoreConfig();
    	$arr = explode("/", $this->http_server);
        $domain = UrlModeAbstract::containsWWW($this->http_server) ? 'www.'. UrlModeFactory::$DOMAIN : UrlModeFactory::$DOMAIN;
        $base = !UrlModeAbstract::isRootInstalled() ? UrlModeAbstract::getInstallationDir() : $domain;
    	while (array_pop($arr) != $base) { }  // unusual case
        $ssl = $this->registry->get('config')->get('config_secure');
        $html_meta_base = implode("/", $arr) . '/'. $base . '/';
    	$this->html_meta_base = UrlModeAbstract::makeHttps($html_meta_base, $ssl);
    	$this->http_image = $html_meta_base . 'image/';
    	$this->https_image = UrlModeAbstract::makeHttps($this->http_image, $ssl);    	  	
    }
    
    public function loadFromCustomConfig($custom) {
    	parent::loadFromCustomConfig($custom);
    	$arr = explode("/", $this->http_server);
    	array_pop($arr);     	
    	$this->html_meta_base = implode("/", $arr) . '/';
    	$this->http_image = $this->html_meta_base . 'image/';
    	$this->https_image = UrlModeAbstract::makeHttps($this->http_image, $custom['config_secure']);       	 
    }
}
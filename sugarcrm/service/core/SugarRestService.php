<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 *
 ********************************************************************************/

require_once('service/core/SugarWebService.php');
require_once('service/core/SugarRestServiceImpl.php');

/**
 * Base class for rest services
 *
 */
class SugarRestService extends SugarWebService{
	protected $implementationClass = 'SugarRestServiceImpl';
	protected $restURL = "";
	protected $registeredFunc = array();

	/**
	 * Get Sugar REST class name for input/return type
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _getTypeName($name)
	{
		if(empty($name)) return 'SugarRest';

		$name = clean_string($name, 'ALPHANUM');
		$type = '';
		switch(strtolower($name)) {
			case 'json':
				$type = 'JSON';
				break;
			case 'rss':
				$type = 'RSS';
				break;
			case 'serialize':
				$type = 'Serialize';
				break;
		}
		$classname = "SugarRest$type";
		if(!file_exists('service/core/REST/' . $classname . '.php')) {
			return 'SugarRest';
		}
		return $classname;
	}

	/**
	 * Constructor.
	 *
	 * @param String $url - REST url
	 */
	function __construct($url){

        $this->setupRoute('/:module/:action/:id');
        $this->setupRoute('/:module/:record', array('action' => 'id'));
        $this->setupRoute('/:module', array('action' => 'index'));
        $this->setupRoute('/:module/:action/*');

        $url = $this->getUrl();
        if(!is_null($url)){
            $result = $this->parseUrl($url);
             foreach($result as $key => $value){
                $_REQUEST[$key] = $value;
            }
        }
        
		$GLOBALS['log']->info('Begin: SugarRestService->__construct');
		$this->restURL = $url;

		$this->responseClass = $this->_getTypeName($_REQUEST['response_type']);
		$this->serverClass = $this->_getTypeName($_REQUEST['input_type']);
		$GLOBALS['log']->info('SugarRestService->__construct serverclass = ' . $this->serverClass);
		require_once('service/core/REST/'. $this->serverClass . '.php');
		$GLOBALS['log']->info('End: SugarRestService->__construct');
	} // ctor

	/**
	 * Its a no op method
	 *
	 * @access public
	 */
	public function registerType($name, $typeClass, $phpType, $compositor, $restrictionBase, $elements, $attrs, $arrayType){
  	} // fn

  	/**
  	 * This method registers all the functions you want to expose as services with REST
  	 *
  	 * @param String $function - name of the function
  	 * @param Array $input - assoc array of input values: key = param name, value = param type
  	 * @param Array $output - assoc array of output values: key = param name, value = param type
	 * @access public
  	 */
	function registerFunction($function, $input, $output){
		if(in_array($function, $this->excludeFunctions))return;
		$this->registeredFunc[$function] = array('input'=> $input, 'output'=>$output);
	} // fn

	/**
	 * It passes request data to REST server and sends response back to client
	 * @access public
	 */
	function serve(){
		$GLOBALS['log']->info('Begin: SugarRestService->serve');
		require_once('service/core/REST/'. $this->responseClass . '.php');
		$response  = $this->responseClass;

		$responseServer = new $response($this->implementation);
		$this->server->faultServer = $responseServer;
		$this->responseServer->faultServer = $responseServer;
		$responseServer->generateResponse($this->server->serve());
		$GLOBALS['log']->info('End: SugarRestService->serve');
	} // fn

	/**
	 * Enter description here...
	 *
	 * @param Array $excludeFunctions - All the functions you don't want to register
	 */
	function register($excludeFunctions = array()){

	} // fn

	/**
	 * This mehtod returns registered implementation class
	 *
	 * @return String - implementationClass
	 * @access public
	 */
	public function getRegisteredImplClass() {
		return $this->implementationClass;
	} // fn

	/**
	 * This mehtod returns registry class
	 *
	 * @return String - registryClass
	 * @access public
	 */
	public function getRegisteredClass() {
		return $this->registryClass;
	} // fn

	/**
	 * Sets the name of the registry class
	 *
	 * @param String $registryClass
	 * @access public
	 */
	function registerClass($registryClass){
		$this->registryClass = $registryClass;
	}

	/**
	 * This function registers implementation class name and creates an instance of rest implementation class
	 * it will be made on this class object
	 *
	 * @param String $implementationClass
	 * @access public
	 */
	function registerImplClass($className){
		$GLOBALS['log']->info('Begin: SugarRestService->registerImplClass');
		$this->implementationClass = $className;
		$this->implementation = new $this->implementationClass();
		$this->server = new $this->serverClass($this->implementation);
		$this->server->registerd = $this->registeredFunc;
		$GLOBALS['log']->info('End: SugarRestService->registerImplClass');
	} // fn

	/**
	 * This function sets the fault object on the REST
	 *
	 * @param SoapError $errorObject - This is an object of type SoapError
	 * @access public
	 */
	function error($errorObject){
		$GLOBALS['log']->info('Begin: SugarRestService->error');
		$this->server->fault($errorObject);
		$GLOBALS['log']->info('Begin: SugarRestService->error');
	} // fn

	/**
	 * This mehtod returns server
	 *
	 * @return String - server
	 * @access public
	 */
	function getServer(){
		return $this->server;
	} // fn




    //ROGER
    protected $_validExtensions = array('json', 'xml');
    //not used yet
    protected $_verbMap = array('GET' => array('action' => 'record'),
                                'POST' => array('action' => 'edit'),
                                'DELETE', array('action' => 'delete')
                                );

    /*
     * Return the url parameter from the GET request
     */
    public function getUrl(){
        if (!empty($_GET['url'])) {
            $url = $_GET['url'];
            if ($url{0} == '/') {
			    $url = substr($url, 1);
		    }
            if (strpos($url, 'index.php') !== false) {
                return null;
            }
            return $url;
        }
        return null;
    }

    public function parseUrl($url){
        if ($url && strpos($url, '/') !== 0) {
			$url = '/' . $url;
		}
		if (strpos($url, '?') !== false) {
			$url = substr($url, 0, strpos($url, '?'));
		}

        $result = $this->parseExtension($url);
        $url = $result['url'];
        $ext = $result['ext'];
        //do not proceed down this path if we do not have an extension,
        //in the future we might be able to do something here, but right now if use this method
        //for non-ajax calls then the images need the root / in order to work.
        if(empty($ext)){
            return array();
        }

        $path = explode('/', $url);


        $action = "index";
        $params = array();

        foreach($this->routes as $route){
            $useRoute = true;
            list($route, $mapping) = $route;

            $routeElements = explode("/",$route);
            $path = array_pad($path, count($routeElements), '');

            $params['action']= 'index';
            if(is_array($mapping)){
                $params = $mapping;
            }
            if(!empty($ext)){
                $params['response_type'] = $ext;
                $params['input_type'] = $ext;
            }

            $i = 0;

            foreach($routeElements as $routeElement){
                if (substr($routeElement,0,1) == ":") {
                    if(empty($path[$i])){
                        $useRoute = false;
                        break;
                    }
					$params[substr($routeElement,1)] = $path[$i];
                }elseif ($routeElement == "*") {
                    $params[] = $path[$i];
                }elseif($routeElement != $path[$i]){
                    $useRoute = false;
                    break;

                }
                $i++;
            }
            if($useRoute){
                return $params;
            }
        }
        return array();
    }

    public function parseRoute($route){
        $elements = explode('/', $route);
        foreach ($elements as $element) {
			if (empty($element)) {
				continue;
			}
			$q = null;
			$element = trim($element);
			$namedParam = strpos($element, ':') !== false;
        }
    }

    public function parseExtension($url){
        $ext ='';
        if (preg_match('/\.[0-9a-zA-Z]*$/', $url, $match) === 1) {
            $match = substr($match[0], 1);
            if (empty($this->_validExtensions)) {
			    $url = substr($url, 0, strpos($url, '.' . $match));
				$ext = $match;
			}else {
			    foreach ($this->_validExtensions as $name) {
                    if (strcasecmp($name, $match) === 0) {
					    $url = substr($url, 0, strpos($url, '.' . $name));
                        $ext = $match;
						break;
					}
				}
			}
        }
        return array('url' => $url, 'ext' => $ext);
    }

    public function setupRoute($route, $defaultMap = array()){
        $this->routes[] = array($route, $defaultMap);
    }


}

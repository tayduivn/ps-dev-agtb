<?php

require_once ('include/externalAPI/Base/ExternalAPIPlugin.php');
require_once ('include/externalAPI/Base/ExternalOAuthAPIPlugin.php');
require_once('include/connectors/sources/SourceFactory.php');

abstract class ExternalAPIBase implements ExternalAPIPlugin
{
    public $account_name;
    public $account_password;
    public $authMethod = 'password';
    public $useAuth = true;
    public $requireAuth = true;
    /**
     * Authorization data
     * @var EAPM
     */
    protected $authData;

    /**
     * Load authorization data
     * @param EAPM $eapmBean
     * @see ExternalAPIPlugin::loadEAPM()
     */
    public function loadEAPM($eapmBean)
    {
        // FIXME: check if the bean is validated, if not, refuse it?
        $this->eapmBean = $eapmBean;
        if ($this->authMethod == 'password') {
            $this->account_name = $eapmBean->name;
            $this->account_password = $eapmBean->password;
        }
        return true;
    }

    /**
     * Check login
     * @param EAPM $eapmBean
     * @see ExternalAPIPlugin::checkLogin()
     */
    public function checkLogin($eapmBean = null)
    {
        if(!empty($eapmBean)) {
            $this->loadEAPM($eapmBean);
        }

        if ( !isset($this->eapmBean) ) {
            return array('success' => false);
        }

        return array('success' => true);
    }

    public function quickCheckLogin()
    {
        if ( !isset($this->eapmBean) ) {
            return array('success' => false, 'errorMessage' => translate('LBL_ERR_NO_AUTHINFO','EAPM'));
        }

        return array('success' => true);
    }

    protected function getValue($value)
    {
        if(!empty($this->$value)) {
            return $this->$value;
        }
        return null;
    }

    public function logOff()
    {
        // Not sure if we should do anything.
        return true;
    }

    /**
     * Does API support this method?
     * @see ExternalAPIPlugin::supports()
     */
    public function supports($method = '')
	{
        return $method==$this->authMethod;
	}

	protected function postData($url, $postfields, $headers)
	{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ( ( is_array($postfields) && count($postfields) == 0 ) ||
             empty($postfields) ) {
            curl_setopt($ch, CURLOPT_POST, false);
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $GLOBALS['log']->debug("ExternalAPIBase->postData Where: ".$url);
        $GLOBALS['log']->debug("Headers:\n".print_r($headers,true));
        // $GLOBALS['log']->debug("Postfields:\n".print_r($postfields,true));
        $rawResponse = curl_exec($ch);
        $GLOBALS['log']->debug("Got:\n".print_r($rawResponse,true));

        return $rawResponse;
	}

	/**
	 * Get connector for this API
	 * @return source|null
	 */
	public function getConnector()
	{
	    if(isset($this->connector)) {
	        if(empty($this->connector_source)) {
	            $this->connector_source = SourceFactory::getSource($this->connector, false);
	        }
	        return $this->connector_source;
	    }
	    return null;
	}

	/**
	 * Get parameter from source
	 * @param string $name
	 * @return mixed
	 */
	public function getConnectorParam($name)
	{
        $connector =  $this->getConnector();
        if(empty($connector)) return null;
        return $connector->getProperty($name);
	}
}

<?php

require_once ('include/externalAPI/Base/ExternalAPIPlugin.php');
require_once ('include/externalAPI/Base/ExternalOAuthAPIPlugin.php');

abstract class ExternalAPIBase implements ExternalAPIPlugin, ExternalOAuthAPIPlugin
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
        $this->authData = $eapmBean;
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
        $this->checkOauthLogin();
    }

    protected function checkOauthLogin()
    {
        if(empty($this->authData)) return;

        if($this->authMethod == 'oauth') {
            if(empty($this->authData->oauth_token) || empty($this->authData->oauth_secret)) {
                $this->authData->oauthLogin($this);
            }
        }
    }

    protected function getValue($value)
    {
        if(!empty($this->$value)) {
            return $this->$value;
        }
        return null;
    }

    public function getOauthParams()
    {
        return $this->getValue("oauthParams");
    }

    public function getOauthRequestURL()
    {
        return $this->getValue("oauthReq");
    }

    public function getOauthAuthURL()
    {
        return $this->getValue("oauthAuth");
    }

    public function getOauthAccessURL()
    {
        return $this->getValue("oauthAccess");
    }

    public function logOff()
    {
        // Not sure if we should do anything.
        return true;
    }

    public function supports($method = '')
	{
        return $method==$this->authMethod;
	}

	protected function postData($url, $postfields, $headers)
	{
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $GLOBALS['log']->fatal("Where: ".$url);
        $GLOBALS['log']->fatal("Sent:\n".print_r($data,true));
        $rawResponse = curl_exec($ch);
        $GLOBALS['log']->fatal("Got:\n".print_r($rawResponse,true));

        return $rawResponse;
	}
}

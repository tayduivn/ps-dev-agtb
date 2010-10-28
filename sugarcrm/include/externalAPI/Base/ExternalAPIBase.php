<?php

require_once ('include/externalAPI/Base/ExternalAPIPlugin.php');

abstract class ExternalAPIBase implements ExternalAPIPlugin
{
    public $account_name;
    public $account_password;
    public $authMetods = array();
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
        if (!$this->supports($eapmBean->type)) {
            // FIXME: produce error message for the user
            $GLOBALS['log']->fatal("Unknown auth type: {$eapmBean->type}");
            return false;
        }
        // FIXME: check if the bean is validated, if not, refuse it?
        $this->authData = $eapmBean;
        if ($eapmBean->type == 'password') {
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

        if($this->authData->type == 'oauth') {
            if(empty($this->authData->oauth_token) || empty($this->authData->oauth_secret)) {
                $this->authData->oauthLogin($this->oauthReq, $this->oauthAuth, $this->oauthAccess);
            }
        }
    }

    public function logOff()
    {
        // Not sure if we should do anything.
        return true;
    }

    public function supports($method = '')
	{
	    return empty($method)?$this->authMethods:isset($this->authMethods[$method]);
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
	}
}

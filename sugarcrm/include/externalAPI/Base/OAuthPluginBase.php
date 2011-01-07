<?php
require_once('include/externalAPI/Base/ExternalAPIBase.php');

class OAuthPluginBase extends ExternalAPIBase implements ExternalOAuthAPIPlugin {
    public $authMethod = 'oauth';
    
    public function loadEAPM($eapmBean)
    {
        if ( !parent::loadEAPM($eapmBean) ) { return false; }

        $this->oauth_token = $eapmBean->oauth_token;
        $this->oauth_secret = $eapmBean->oauth_secret;

        return true;
    }

    /**
     * Check login
     * @param EAPM $eapmBean
     * @see ExternalAPIBase::checkLogin()
     */
    public function checkLogin($eapmBean = null)
    {
        $reply = parent::checkLogin($eapmBean);
        if ( !$reply['success'] ) {
            return $reply;
        }
        
        if ( $this->checkOauthLogin() ) {
            return array('success' => true);
        }
    }    

    protected function checkOauthLogin()
    {
        if ( empty($this->oauth_token) || empty($this->oauth_secret) ) {
            return $this->oauthLogin();
        } else {
            return false;
        }
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

    /**
     * Get OAuth client
     * @return SugarOauth
     */
    public function getOauth()
    {
        $oauth = new SugarOAuth($this->oauthParams['consumerKey'], $this->oauthParams['consumerSecret'], $this->getOauthParams());
        
        if ( isset($this->oauth_token) && !empty($this->oauth_token) ) {
            $oauth->setToken($this->oauth_token, $this->oauth_secret);
        }
        
        return $oauth;
    }

   public function oauthLogin()
   {
        global $sugar_config;
        $oauth = $this->getOauth();
        if(isset($_SESSION['eapm_oauth_secret']) && isset($_SESSION['eapm_oauth_token']) && isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier'])) {
            $stage = 1;
        } else {
            $stage = 0;
        }
        if($stage == 0) {
            $oauthReq = $this->getOauthRequestURL();
            $callback_url = $sugar_config['site_url'].'/index.php?module=EAPM&action=oauth&record='.$this->eapmBean->id;
            $GLOBALS['log']->debug("OAuth request token: {$oauthReq} callback: $callback_url");
            $request_token_info = $oauth->getRequestToken($oauthReq, $callback_url);
            $GLOBALS['log']->debug("OAuth token: ".var_export($request_token_info, true));
            // FIXME: error checking here
            $_SESSION['eapm_oauth_secret'] = $request_token_info['oauth_token_secret'];
            $_SESSION['eapm_oauth_token'] = $request_token_info['oauth_token'];
            $authReq = $this->getOauthAuthURL();
            SugarApplication::redirect("{$authReq}?oauth_token={$request_token_info['oauth_token']}");
        } else {
            $accReq = $this->getOauthAccessURL();
            $oauth->setToken($_SESSION['eapm_oauth_token'],$_SESSION['eapm_oauth_secret']);
            $GLOBALS['log']->debug("OAuth access token: {$accReq}");
            $access_token_info = $oauth->getAccessToken($accReq);
            $GLOBALS['log']->debug("OAuth token: ".var_export($access_token_info, true));
            // FIXME: error checking here
            $this->oauth_token = $access_token_info['oauth_token'];
            $this->oauth_secret = $access_token_info['oauth_token_secret'];
            $this->eapmBean->oauth_token = $this->oauth_token;
            $this->eapmBean->oauth_secret = $this->oauth_secret;
            $oauth->setToken($this->oauth_token, $this->oauth_secret);
            $this->eapmBean->validated = 1;
            $this->eapmBean->save();
            unset($_SESSION['eapm_oauth_token']);
            unset($_SESSION['eapm_oauth_secret']);
            return true;
        }
        return false;
	}


    
}
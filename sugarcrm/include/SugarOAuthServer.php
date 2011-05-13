<?php

require_once 'modules/OAuthTokens/OAuthToken.php';
require_once 'modules/OAuthKeys/OAuthKey.php';
/**
 *
 * Sugar OAuth provider implementation
 */
class SugarOAuthServer
{
    /**
     * OAuth token
     * @var OAuthToken
     */
    protected $token;

    /**
     * Check if everything is OK
     * @throws OAuthException
     */
    protected function check()
    {
        if(!extension_loaded('oauth')) {
            // define exception class
            throw new OAuthException("OAuth extension required for OAuth support");
        }
    }

    /**
     * Find consumer by key
     * @param $provider
     */
    public function lookupConsumer($provider)
    {
        // check $provider->consumer_key
        // on unknown: OAUTH_CONSUMER_KEY_UNKNOWN
        // on bad key: OAUTH_CONSUMER_KEY_REFUSED
        $GLOBALS['log']->debug("OAUTH: lookupConsumer, key={$provider->consumer_key}");
        $consumer = OAuthKey::fetchKey($provider->consumer_key);
        if(!$consumer) {
            return OAUTH_CONSUMER_KEY_UNKNOWN;
        }
        $provider->consumer_secret = $consumer->c_secret;
        $this->consumer = $consumer;
        return OAUTH_OK;
    }

    /**
     * Check timestamps & nonces
     * @param OAuthProvider $provider
     */
    public function timestampNonceChecker($provider)
    {
        // FIXME: add ts/nonce verification
        if(empty($provider->nonce)) {
            return OAUTH_BAD_NONCE;
        }
        if(empty($provider->timestamp)) {
            return OAUTH_BAD_TIMESTAMP;
        }
        return OAuthToken::checkNonce($provider->consumer_key, $provider->nonce, $provider->timestamp);
    }

    /**
     * Vefiry incoming token
     * @param OAuthProvider $provider
     */
    public function tokenHandler($provider)
    {
        $GLOBALS['log']->debug("OAUTH: tokenHandler, token={$provider->token}, verify={$provider->verifier}");

        $token = OAuthToken::load($provider->token);
        if(empty($token)) {
            return OAUTH_TOKEN_REJECTED;
        }
        if($token->consumer != $provider->consumer_key) {
            return OAUTH_TOKEN_REJECTED;
        }
        $GLOBALS['log']->debug("OAUTH: tokenHandler, found token=".var_export($token->id, true));
        if($token->tstate == OAuthToken::REQUEST) {
            if(!empty($token->verify) && $provider->verifier == $token->verify) {
                $provider->token_secret = $token->secret;
                $this->token = $token;
                return OAUTH_OK;
            } else {
                return OAUTH_TOKEN_USED;
            }
        }
        if($token->tstate == OAuthToken::ACCESS) {
            $provider->token_secret = $token->secret;
            $this->token = $token;
            return OAUTH_OK;
        }
        return OAUTH_TOKEN_REJECTED;
    }

    /**
     * Create OAuth provider
     *
     * Checks current request for OAuth valitidy
     * @param bool $add_rest add REST endpoint as request path
     */
    public function __construct($req_path = '')
    {
        $GLOBALS['log']->debug("OAUTH: __construct($req_path): ".var_export($_REQUEST, true));
        $this->check();
        $this->provider = new OAuthProvider();
        try {
		    $this->provider->consumerHandler(array($this,'lookupConsumer'));
		    $this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
		    $this->provider->tokenHandler(array($this,'tokenHandler'));
	        if(!empty($req_path)) {
		        $this->provider->setRequestTokenPath($req_path);  // No token needed for this end point
	        }
	    	$this->provider->checkOAuthRequest();
	    	if(mt_rand() % 10 == 0) {
	    	    // cleanup 1 in 10 times
	    	    OAuthToken::cleanup();
	    	}
        } catch(Exception $e) {
            $GLOBALS['log']->debug($this->reportProblem($e));
            throw $e;
        }
    }

    /**
     * Generate request token string
     * @return string
     */
    public function requestToken()
    {
        $GLOBALS['log']->debug("OAUTH: requestToken");
        $token = OAuthToken::generate();
        $token->consumer = $this->provider->consumer_key;
        $token->save();
        return $token->queryString();
    }

    /**
     * Generate access token string - must have validated request token
     * @return string
     */
    public function accessToken()
    {
        $GLOBALS['log']->debug("OAUTH: accessToken");
        if(empty($this->token) || $this->token->tstate != OAuthToken::REQUEST) {
            return null;
        }
        $this->token->invalidate();
        $token = OAuthToken::generate();
        $token->setState(OAuthToken::ACCESS);
        $token->consumer = $this->provider->consumer_key;
        // transfer user data from request token
        $token->copyAuthData($this->token);
        $token->save();
        return $token->queryString();
    }

    /**
     * Return authorization URL
     * @return string
     */
    public function authUrl()
    {
        return urlencode($GLOBALS['sugar_config']['site_url']."index.php?module=OAuthTokens&action=authorize");
    }

    /**
     * Fetch current token if it is authorized
     * @return OAuthToken|null
     */
    public function authorizedToken()
    {
        if($this->token->tstate == OAuthToken::ACCESS) {
            return $this->token;
        }
        return null;
    }

    /**
     * Fetch authorization data from current token
     * @return mixed Authorization data or null if none
     */
    public function authorization()
    {
        if($this->token->tstate == OAuthToken::ACCESS) {
            return $this->token->authdata;
        }
        return null;
    }

    /**
     * Report OAuth problem as string
     */
    public function reportProblem(Exception $e)
    {
        return $this->provider->reportProblem($e);
    }
}

if(!class_exists('OAuthException')) {
    // we will use this in case oauth extension is not loaded
    class OAuthException extends Exception {}
}
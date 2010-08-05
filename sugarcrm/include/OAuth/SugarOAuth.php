<?php

require_once 'include/OAuth/SugarOAuthToken.php';

class SugarOAuth
{
    /**
     * Sugar configuration
     * @var array
     */
    public $config;

    /**
     * OAuth token
     * @var SugarOAuthToken
     */
    protected $token;

    public static function requirementCheck()
    {
        return extension_loaded("oauth") && extension_loaded("mongo");
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
        $provider->consumer_secret = "CONSUMERSECRET";
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
        return SugarOAuthToken::checkNonce($provider->consumer_key, $provider->nonce, $provider->timestamp);
    }

    /**
     * Vefiry incoming token
     * @param OAuthProvider $provider
     */
    public function tokenHandler($provider)
    {
        $GLOBALS['log']->debug("OAUTH: tokenHandler, token={$provider->token}, verify={$provider->verifier}");

        $token = SugarOAuthToken::load($provider->token);
        if(empty($token)) {
            return OAUTH_TOKEN_REJECTED;
        }
        $GLOBALS['log']->debug("OAUTH: tokenHandler, found token=".var_export($token, true));
        if($token->state == SugarOAuthToken::REQUEST) {
            if(!empty($token->verify) && $provider->verifier == $token->verify) {
                $provider->token_secret = $token->secret;
                $this->token = $token;
                return OAUTH_OK;
            } else {
                return OAUTH_TOKEN_USED;
            }
        }
        if($token->state == SugarOAuthToken::ACCESS) {
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
    public function __construct($add_rest = false)
    {
        global $sugar_config;
        $this->config = $sugar_config;
        $GLOBALS['log']->debug("OAUTH: __construct: ".var_export($_REQUEST, true));
        $this->provider = new OAuthProvider();
        try {
		    $this->provider->consumerHandler(array($this,'lookupConsumer'));
		    $this->provider->timestampNonceHandler(array($this,'timestampNonceChecker'));
		    $this->provider->tokenHandler(array($this,'tokenHandler'));
	        if($add_rest) {
		        $this->provider->setRequestTokenPath('/sugarent/service/v3/rest.php');  // No token needed for this end point
	        }
	    	$this->provider->checkOAuthRequest();
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
        $token = SugarOAuthToken::generate();
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
        if($this->token->state != SugarOAuthToken::REQUEST) {
            return null;
        }
        $this->token->invalidate();
        $token = SugarOAuthToken::generate();
        $token->state = SugarOAuthToken::ACCESS;
        // transfer user data from request token
        $token->copyAuthData($this->token);
        $token->save();
        return $token->queryString();
    }

    /**
     * Authorize token and attach auth data
     * @param string $token
     * @param mixed $authdata
     * @return string Verification code for generating access token
     */
    public static function authorize($token, $authdata)
    {
        $token = SugarOAuthToken::load($token);
        $token->authorize($authdata);
        return $token->verify;
    }

    /**
     * Fetch authorization data from current token
     * @return mixed Authorization data or null if none
     */
    public function authorization()
    {
        if($this->token->state == SugarOAuthToken::ACCESS) {
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

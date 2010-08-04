<?php

class SugarOAuth
{
    /**
     * Sugar configuration
     * @var array
     */
    public $config;

    /**
     * OAuth token
     * @var OAuthToken
     */
    protected $token;

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

    public function timestampNonceChecker($provider)
    {
         return OAUTH_OK;
    }

    public function tokenHandler($provider)
    {
        $GLOBALS['log']->debug("OAUTH: tokenHandler, token={$provider->token}, verify={$provider->verifier}");

        $token = OAuthToken::load($provider->token);
        if(empty($token)) {
            return OAUTH_TOKEN_REJECTED;
        }
        $GLOBALS['log']->debug("OAUTH: tokenHandler, found token=".var_export($token, true));
        if($token->state == OAuthToken::REQUEST) {
            if(!empty($token->verify) && $provider->verifier == $token->verify) {
                $provider->token_secret = $token->secret;
                $token->invalidate();
                $this->token = $token;
                return OAUTH_OK;
            } else {
                return OAUTH_TOKEN_USED;
            }
        }
        if($token->state == OAuthToken::ACCESS) {
            $provider->token_secret = $token->secret;
            $this->token = $token;
            return OAUTH_OK;
        }
        return OAUTH_TOKEN_REJECTED;
    }

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

    public function requestToken()
    {
        $GLOBALS['log']->debug("OAUTH: requestToken");
        $token = OAuthToken::generate();
        $token->save();
        return $token->queryString();
    }

    public function accessToken()
    {
        $GLOBALS['log']->debug("OAUTH: accessToken");
        $token = OAuthToken::generate();
        $token->state = OAuthToken::ACCESS;
        // transfer user data from request token
        $token->copyAuthData($this->token);
        $token->save();
        return $token->queryString();
    }

    public static function authorize($token, $authdata)
    {
        $token = OAuthToken::load($token);
        $token->authorize($authdata);
        return $token->verify;
    }

    public function authorization()
    {
        if($this->token->state == OAuthToken::ACCESS) {
            return $this->token->authdata;
        }
        return null;
    }

    public function reportProblem($e)
    {
        return $this->provider->reportProblem($e);
    }
}

class OAuthToken
{

    protected $data = array();
    protected static $mongo;

    const REQUEST = 1;
    const ACCESS = 2;
    const INVALID = 3;

    function __construct($token, $secret)
	{
        $this->data['token'] = $token;
        $this->data['secret'] = $secret;
        $this->setState(self::REQUEST);
	}

	public function __get($var)
	{
	    if(!isset($this->data[$var])) return null;
	    return $this->data[$var];
	}

	public function __set($var, $val)
	{
	    $this->data[$var] = $val;
	}

	public function __isset($var)
	{
	    return isset($this->data[$var]);
	}

	public function __unset($var)
	{
	    unset($this->data[$var]);
	}

	public function setState($s)
	{
	    $this->data['state'] = $s;
	    return $this;
	}

	protected static function randomValue()
	{
	    return bin2hex(OAuthProvider::generateToken(6));
	}

    static function generate()
    {
        $t = self::randomValue();
        $s = self::randomValue();
        return new self($t, $s);
    }

    public function save()
    {
        return self::getTable()->update(array("token" => $this->token), $this->data, array("upsert" => true));
    }

    public static function getTable()
    {
        if(!isset(self::$mongo)) {
            self::$mongo = new Mongo();
            self::$mongo->connect();
        }
        return self::$mongo->oauth->oauth_tokens;
    }

    static function load($token)
	{
        $data = self::getTable()->findOne(array("token" => $token));
        if(empty($data)) return null;
        $t = new self($data['token'], $data['secret']);
	    foreach($data as $k => $v) {
	        $t->$k = $v;
	    }
	    return $t;
	}

	public function invalidate()
	{
	    $this->state = self::INVALID;
	    unset($this->verify);
	    return $this->save();
	}

	public function authorize($authdata)
	{
	    // TODO: add user data
	    $this->verify = self::randomValue();
	    $this->authdata = $authdata;
	    $this->save();
	    return $this;
	}

	public function copyAuthData(OAuthToken $token)
	{
	    // TODO: copy data from $token
	    $this->authdata = $token->authdata;
	    return $this;
	}

	public function queryString()
	{
	    return "oauth_token={$this->token}&oauth_token_secret={$this->secret}";
	}
}

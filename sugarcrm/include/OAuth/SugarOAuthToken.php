<?php

class SugarOAuthToken
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
        $this->data['ts'] = time();
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
        $t->data = $data;
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

	public function copyAuthData(SugarOAuthToken $token)
	{
	    // TODO: copy data from $token
	    $this->authdata = $token->authdata;
	    return $this;
	}

	public function queryString()
	{
	    return "oauth_token={$this->token}&oauth_token_secret={$this->secret}";
	}

	public function cleanup()
	{
	    // delete invalidated tokens older than 1 day
	    return self::getTable()->remove(array("status" => self::INVALID, "ts" => array('$lt' => time()-60*60*24)));
	    // delete request tokens older than 1 day
	    return self::getTable()->remove(array("status" => self::REQUEST, "ts" => array('$lt' => time()-60*60*24)));
	}
}

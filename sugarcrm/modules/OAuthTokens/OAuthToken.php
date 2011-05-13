<?php
require_once 'include/OAuth/SugarOAuthData.php';
require_once 'modules/OAuthKeys/OAuthKey.php';

class OAuthToken extends SugarBean
{
	public $module_dir = 'OAuthTokens';
	public $object_name = 'OAuthToken';
	public $table_name = 'oauth_tokens';
	public $disable_row_level_security = true;

	public $token;
    public $secret;
    public $tstate;
    public $token_ts;
    public $verify;
    public $authdata;
    public $consumer;
    public $assigned_user_id;

    const REQUEST = 1;
    const ACCESS = 2;
    const INVALID = 3;

    function __construct($token='', $secret='')
	{
	    parent::SugarBean();
        $this->token = $token;
        $this->secret = $secret;
        $this->setState(self::REQUEST);
	}

	/**
	 * Set token state
	 * @param int $s
	 * @return OAuthToken
	 */
	public function setState($s)
	{
	    $this->tstate = $s;
	    return $this;
	}

	/**
	 * Generate random token
	 * @return string
	 */
	protected static function randomValue()
	{
	    return bin2hex(OAuthProvider::generateToken(6));
	}

	/**
	 * Generate random token/secret pair and create token
	 * @return OAuthToken
	 */
    static function generate()
    {
        $t = self::randomValue();
        $s = self::randomValue();
        return new self($t, $s);
    }

    public function save()
    {
        $this->token_ts = time();
        if(!isset($this->id)) {
            $this->new_with_id = true;
            $this->id = $this->token;
        }
        parent::save();
    }

    /**
     * Load token by ID
     * @param string $token
	 * @return OAuthToken
     */
    static function load($token)
	{
	    $ltoken = new self();
	    $ltoken->retrieve($token);
        if(empty($ltoken->id)) return null;
        $ltoken->token = $ltoken->id;
        return $ltoken;
	}

	/**
	 * Invalidate token
	 */
	public function invalidate()
	{
	    $this->state = self::INVALID;
	    $this->verify = false;
	    return $this->save();
	}

	/**
	 * Authorize request token
	 * @param mixed $authdata
	 * @return string Validation token
	 */
	public function authorize($authdata)
	{
	    if($this->tstate != self::REQUEST) {
	        return false;
	    }
	    $this->verify = self::randomValue();
	    $this->authdata = $authdata;
	    if(isset($authdata['user'])) {
	        $this->assigned_user_id = $authdata['user'];
	    }
	    $this->save();
	    return $this->verify;
	}

	/**
	 * Copy auth data between tokens
	 * @param OAuthToken $token
	 * @return OAuthToken
	 */
	public function copyAuthData(OAuthToken $token)
	{
	    $this->authdata = $token->authdata;
	    $this->assigned_user_id = $token->assigned_user_id;
	    return $this;
	}

	/**
	 * Get query string for the token
	 */
	public function queryString()
	{
	    return "oauth_token={$this->token}&oauth_token_secret={$this->secret}";
	}

	/**
	 * Clean up stale tokens
	 */
    static public function cleanup()
	{
	    global $db;
	    // delete invalidated tokens older than 1 day
	    $db->query("DELETE FROM oauth_token WHERE status = ".self::INVALID." AND token_ts < ".time()-60*60*24);
	    // delete request tokens older than 1 day
	    $db->query("DELETE FROM oauth_token WHERE status = ".self::REQUEST." AND token_ts < ".time()-60*60*24);
	}

	/**
	 * Check if the nonce is valid
	 * @param string $key
	 * @param string $nonce
	 * @param string $ts
	 */
	public static function checkNonce($key, $nonce, $ts)
	{
	    global $db;

	    $res = $db->query(sprintf("SELECT * FROM oauth_nonce WHERE conskey='%s' AND nonce_ts > %d", $db->quote($key), $ts));
	    if($res && $db->fetchByAssoc($res)) {
	        // we have later ts
	        return OAUTH_BAD_TIMESTAMP;
	    }

	    $res = $db->query(sprintf("SELECT * FROM oauth_nonce WHERE conskey='%s' AND nonce='%s' AND nonce_ts = %d", $db->quote($key), $db->quote($nonce), $ts));
	    if($res && $db->fetchByAssoc($res)) {
	        // Already seen this one
	        return OAUTH_BAD_NONCE;
        }
        $db->query(sprintf("DELETE FROM oauth_nonce WHERE conskey='%s' AND nonce_ts < %d", $db->quote($key), $ts));
        $db->query(sprintf("INSERT INTO oauth_nonce(conskey, nonce, nonce_ts) VALUES('%s', '%s', %d)", $db->quote($key), $db->quote($nonce), $ts));
	    return OAUTH_OK;
	}

	public function mark_deleted($id)
	{
	    $this->db->query("DELETE from {$this->table_name} WHERE id='".$this->db->quote($id)."'");
	}
}

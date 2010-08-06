<?php
require_once 'include/OAuth/SugarOAuthDataDB.php';

class SugarOAuthData
{
    protected static $mongo;
    /**
     * Should we use Mongo or regular DB?
     * @var bool
     */
    protected static $use_db = true;

    public static function getTable($table = "tokens")
    {
        if(self::$use_db) {
            return new SugarOAuthDataDB($table);
        }
        if(!isset(self::$mongo)) {
            self::$mongo = new Mongo();
            self::$mongo->connect();
        }
        return self::$mongo->oauth->$table;
    }

    static public function cleanup()
	{
	    $table = self::getTable();
	    // delete invalidated tokens older than 1 day
	    $table->remove(array("status" => SugarOAuthToken::INVALID, "ts" => array('$lt' => time()-60*60*24)));
	    // delete request tokens older than 1 day
	    $table->remove(array("status" => SugarOAuthToken::REQUEST, "ts" => array('$lt' => time()-60*60*24)));
        $table->ensureIndex(array("token" => 1));
	}

	public static function getConsumer($key)
	{
	    return self::getTable("consumer")->findOne(array("key" => $key));
	}

	public static function registerConsumer($key, $secret, $name = '')
	{
	    $ctable = self::getTable("consumer");
	    $ctable->insert(array("key" => $key, "secret" => $secret, "name" => $name));
	}

	public static function checkNonce($key, $nonce, $ts)
	{
	    $ntable = self::getTable("nonce");
	    $tsbad = $ntable->findOne(array("key" => $key, "ts" => array('$gt' => $ts)));
	    if(!empty($tsbad)) {
	        // we have later ts
	        return OAUTH_BAD_TIMESTAMP;
	    }
	    $data = array("key" => $key, "ts" => $ts, "nonce" => $nonce);
        $nbad = $ntable->findOne($data);
        if(!empty($nbad)) {
            return OAUTH_BAD_NONCE;
        }
	    $ntable->remove(array("key" => $key, "ts" => array('$lt' => $ts)));
        $ntable->insert($data);
	    return OAUTH_OK;
	}

}

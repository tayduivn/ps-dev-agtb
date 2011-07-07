<?php
require_once 'Zend/Oauth/Provider.php';

class OAuthKey extends Basic
{
	public $module_dir = 'OAuthKeys';
	public $object_name = 'OAuthKey';
	public $table_name = 'oauth_consumer';
	public $c_key;
	public $c_secret;
	public $disable_row_level_security = true;

	static public $keys_cache = array();

	/**
	 * Get record by consumer key
	 * @param string $key
	 */
	public function getByKey($key)
	{
	    $this->retrieve_by_string_fields(array("c_key" => $key));
	    if(empty($this->id)) return false;
	    // need this to decrypt the key
        $this->check_date_relationships_load();
	    return $this;
	}

	/**
	 * Fetch customer key by id
	 * @param string $key
	 */
	public static function fetchKey($key)
	{
	    if(isset(self::$keys_cache[$key])) {
	        return self::$keys_cache[$key];
	    }
	    $k = new self();
	    if($k->getByKey($key)) {
	        self::$keys_cache[$key] = $k;
	        return $k;
	    }
	    return false;
	}

	public function mark_deleted($id)
	{
	    $this->db->query("DELETE from {$this->table_name} WHERE id='".$this->db->quote($id)."'");
	    $this->db->query("DELETE from oauth_tokens WHERE consumer='".$this->db->quote($id)."'");
	}

}

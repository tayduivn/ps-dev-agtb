<?php


require_once('custom/modules/Opportunities/RRControllerMetaData.php');


/**
 * IT Request #10970 - Round Robin Controller for 60 min Opps.
 *
 * Sample Usage:
 * 	$rr = new RRController();
 *  $next_user_id = $rr->getNextAssignedUserFromAccountOjbect($account_object_from_opp);
 * 					OR
 * $next_user_id = getNextAssignedUserFromOpportunityOjbect($opp_object);
 */
class RRController
{
	
	private $_users;
	private $_users_cached;
	private $_cache_file_name = "roundrobin.cache.php";
	private $_cache_key = "robin";
	private $debug;
	
	function __construct($debug = FALSE)
	{
		$this->debug = $debug;
		$this->_users = self::_buildUserList();
		$this->_users_cached = self::_buildUserListFromCache();
		
		if($this->debug)
		{
			$current_users = var_export($this->_users, true);
			$this->log("Current users retrieved: $current_users", 'debug');
			$current_cached_users = var_export($this->_users_cached,true);
			$this->log("Current cached users retrieved: $current_cached_users", 'debug');
		}
		
	}
	
	/**
	 * Get the next user to assign the opp to by passing in an account object.
	 *
	 * @param unknown_type $accnt
	 * @return unknown
	 */
	function getNextAssignedUserFromOpportunityOjbect($opp)
	{
		$this->log("Getting next available user from Opportunity objects", 'debug');

		global $round_robin_ids;
		if(!in_array($opp->assigned_user_id, $round_robin_ids)) {
                        $this->log("The user assigned to opportunity {$opp->id} should not be round robin'd");
                        return FALSE;
                }
		
		$account_id = $opp->account_id;
		if(empty($account_id))
		{
			$this->log("No account associated to opportunity: {$opp->id}, unable to continue");
			return FALSE;
		}
		$accnt = new Account();
		$accnt->retrieve($account_id);
		
		return $this->getNextAssignedUserFromAccountOjbect($accnt);
		
	}
	
	/**
	 * Get the next user to assign the opp to by passing in an account object.
	 *
	 * @param unknown_type $accnt
	 * @return unknown
	 */
	function getNextAssignedUserFromAccountOjbect($accnt)
	{
		$country = $accnt->billing_address_country;
		$state = $accnt->billing_address_state;
		$region = $this->getRegionFromCountry($country, $state);
		if($region === FALSE)
		{
			$this->log("No region found for country: $country.  Unable to continue.");
			return FALSE;	
		}
		
		$next_user_id = $this->getNextAvailableUser($country,$region);
		$this->log("Returning user id: $next_user_id", 'debug');
		
		return $next_user_id;
	}
	
	/**
	 * Given a country and state, determine the region that will be used when grabbing the user.
	 *
	 * @param unknown_type $country
	 */
	function getRegionFromCountry($country = "",$state = "")
	{
		$this->log("Begining to retrieve region for country: $country", "debug");
		if(empty($country))
		{
			$this->log("Empty country, can not retrieve next assigned user");
			return FALSE;	
		}
	
		$country = trim(strtoupper($country));
		$state = trim(strtoupper($state));
		$function_to_call = "getRegionForCountry$country";
		return $this->$function_to_call($country,$state);
	}
	
	/**
	 * For USA, logic to determine which region should be used when grabbing the next user.
	 *
	 * @param unknown_type $country
	 * @param unknown_type $state
	 * @return unknown
	 */
	function getRegionForCountryUSA_old($country,$state)
	{
		global $usa_state_to_region;
		$this->log("Begining to retrieve region from USA mapping file.", "debug");
		//Use our own mapping file, leads routing files were stale and didn't contain 3 regions (SOUTHEAST, EAST, WEST)
		
		if( !isset($usa_state_to_region) || !isset($usa_state_to_region[$state]) )
		{
			$this->log("Unable to retrieve region for country=USA with state=$state", "debug");
			return FALSE;
		}
		else 
		{
			$found_region = $usa_state_to_region[$state];
			$this->log("Found region: $found_region for USA, $state",'debug'); 
		}
		return $found_region;
	}
	
	function getRegionForCountryUSA($country,$state)
        {
		require_once('custom/si_custom_files/meta/leadRoundRobinMap.php');
                $this->log("Begining to retrieve region from USA mapping file.", "debug");
                //Use our own mapping file, leads routing files were stale and didn't contain 3 regions (SOUTHEAST, EAST, WEST)
		
		$usa_state_to_region = $leadRoundRobinMap[$country][$state];

                if( !isset($leadRoundRobinMap) || !isset($usa_state_to_region) )
                {
                        $this->log("Unable to retrieve region for country=USA with state=$state", "debug");
                        return FALSE;
                }
                else
                {
                        $found_region = $usa_state_to_region;
                        $this->log("Found region: $found_region for USA, $state",'debug');
                }
                return $found_region;
        }

	/**
	 * Catch any bad region for country calls.
	 *
	 * @param unknown_type $method
	 * @param unknown_type $arguments
	 * @return unknown
	 */
	function __call($method, $arguments)
	{
		if(stristr($method, 'getRegionForCountry'))
		{
			$this->log("Tried Calling function $method which does not exist, unable to continue.");	
			return FALSE;
		}
	}
	/**
	 * Get the id of the next available user that can be assigned to a case.  Boolean false is returned if no users exist.
	 *
	 * @return  User id or boolean false for no users available.
	 */
	function getNextAvailableUser($country = "", $region = "")
	{
		$user_id_results = FALSE;
		$country = strtoupper($country);
		$region = strtoupper($region);
		
		if(empty($region))
		{
			$this->log("Empty region passed in, could not assign a user");
			return FALSE;	
		}
	
		//Determine if we can use the existing cache file or if new users have been added and we need to ignore the cache file
		if( ! $this->_shouldClearUserList() )
		{
			$this->log("Cached user list and current user list are identical, using cache.", 'debug');
			$this->_users = $this->_users_cached;
		}
		else 
			$this->log("Cached user list and current user list are NOT identical, ignoring cache.", 'debug');
			
			
		if( isset($this->_users[$country][$region]) && ( count($this->_users[$country][$region]) > 0 ) )
		{
			//Grab the first user in the list which we will return and then push it to the end of the array.
			$user_item = array_shift($this->_users[$country][$region]);
			$user_id_results = $user_item['user_id'];
			array_push($this->_users[$country][$region], $user_item);
			$this->_saveRobinResults($this->_users);
			//User cache will be saved but incase this method is called several times without re-instantiating set the user_cache to be fresh.
			$this->_users_cached = $this->_users;
		}
		else 
		{
			$this->log("No users found for country: $country, region $region");
			return FALSE;	
		}
		
		$this->log("Found user: $user_id_results", 'debug');
		return $user_id_results;
			
	}
	
	/**
	 * Retreive the user list.  Currently the user list is stored in a metadata file but this method
	 * can easily be updated to provide additional logic or processing when building the list.
	 *
	 */
	private function _buildUserList()
	{
		global $region_to_user_mapping;
		
		if(empty($region_to_user_mapping))
		{
			$this->log("Unable to build users mapping.");
			$region_to_user_mapping = FALSE;
		}
		
		return $region_to_user_mapping;
	}

	/**
	 * Get user list from cache
	 *
	 * @return unknown
	 */
	private function _buildUserListFromCache()
	{
		return $this->_getCacheValue( $this->_cache_file_name, $this->_cache_key);
	}
	 
	/** Begin Private Helper Functions **/
	
	
	/**
	 * Check to see if we should clear the current cached user list.  This needs to be done if new users are added
	 * or removed.
	 *
	 * @return unknown
	 */
	function _shouldClearUserList()
	{
		
		$a_all_users = $this->_getAllUserIdsForRegion($this->_users);
		$a_all_users_cached = $this->_getAllUserIdsForRegion($this->_users_cached);
		
		$clear_user_list = !($a_all_users == $a_all_users_cached);
		$this->log("Results from should clear user list: $clear_user_list", 'debug');
		return $clear_user_list;
	}
	
	/**
	 * Flatens the user array so it can be compared and the output is just a single array with key/values set to the user ids.
	 *
	 * @param unknown_type $a_users
	 * @return unknown
	 */
	function _getAllUserIdsForRegion($a_users)
	{
		
		if(empty($a_users))
			return array();
			
		$results = array();
		foreach ($a_users as $country => $region)
		{
			foreach ($region as $users)
			{
				foreach ($users as $single_user)
					$results[$single_user['user_id']] = $single_user['user_id'];
			}
		}
		asort($results);	
		return $results;
	}
	/**
	 * Helper function to execute a count query or any query returning a single row.
	 *
	 * @param unknown_type $query
	 * @return unknown
	 */
	private function _executeSingleCntQuery($query)
	{
		$result = $this->_db->query($query);
		return  $this->_db->fetchByAssoc($result);
	}
	
	/**
	 * Helper function to return the result set from a query.
	 *
	 * @param unknown_type $query
	 * @return unknown
	 */
	private function _executeQuery($query)
	{
		$result = $this->_db->query($query);
		$data = array();
		while($row = $this->_db->fetchByAssoc($result)){
			$data[] = $row;
		}
		
		return $data;
	}

	/**
	 * Save the current user array to disk.
	 *
	 * @param unknown_type $lastRobin
	 */
	private function _saveRobinResults($lastRobin) 
	{
	    global $sugar_config;
	    $cacheFolderPath = clean_path("{$sugar_config['cache_dir']}modules/Opportunities");
	    if (!file_exists($cacheFolderPath)) {
	    	mkdir_recursive($cacheFolderPath);
	    }
		$this->_writeCacheFile($this->_cache_key, $lastRobin, $this->_cache_file_name);
	}

	/**
	 * Writes caches to flat file in cache dir.
	 * @param string $key Key to the main cache entry (not timestamp)
	 * @param mixed $var Variable to be cached
	 * @param string $file Cache file name
	 */
	private function _writeCacheFile($key, $var, $file) {
		global $sugar_config;

		$the_file = clean_path("{$sugar_config['cache_dir']}/modules/Opportunities/{$file}");
		$timestamp = strtotime('now');
		$array = array();
		$array['timestamp'] = $timestamp;
		$array[$key] = serialize($var); // serialized since varexport_helper() can't handle PHP objects

		return $this->_writeCacheFileToDisk($array, $the_file);
	}

	/**
	 * Performs the actual file write.  Abstracted from writeCacheFile() for
	 * flexibility
	 * @param array $array The array to write to the cache
	 * @param string $file Full path (relative) with cache file name
	 * @return bool
	 */
	function _writeCacheFileToDisk($array, $file) {
		global $sugar_config;

		$arrayString = var_export_helper($array);

		$date = date("r");
	    $the_string =<<<eoq
<?php // created: {$date}
	\$cacheFile = {$arrayString};
?>
eoq;
	    if($fh = @sugar_fopen($file, "w")) 
	    {
	        fputs($fh, $the_string);
	        fclose($fh);
	        return true;
	    } 
	    else 
	    {
			$this->log("Could not write cache file [ {$file} ]");
	        return false;
	    }
	}

	/**
	 * retrieves the cached value
	 * @param string $file The cachefile name
	 * @param string $key name of cache value
	 * @return mixed
	 */
	private function _getCacheValue($file, $key) {
		global $sugar_config;

		$cacheFilePath = "{$sugar_config['cache_dir']}modules/Opportunities/{$file}";
		$cacheFile = array();

		if(file_exists($cacheFilePath)) 
		{
			include($cacheFilePath); // provides $cacheFile

			if(isset($cacheFile[$key])) 
			{
				$ret = unserialize($cacheFile[$key]);
				return $ret;
			}
		} 
		else 
		{
			$this->log("Cache file not found [ {$cacheFilePath} ] - creating blank cache file",'debug');
		}

		return FALSE;
	}

	/**
	 * Custom log, logs to screen if debug is on, otherwise to sugar log file.
	 *
	 * @param unknown_type $message
	 * @param unknown_type $level
	 */
	private function log($message,$level = 'fatal')
	{
		if($this->debug)
		{
			echo "<pre>$level $message</pre><br/>";
		}
		$GLOBALS['log']->$level("RRController: $message");
	}
}

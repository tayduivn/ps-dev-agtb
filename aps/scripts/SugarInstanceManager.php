<?php

class SugarInstanceManager{
	
	// variables used to store environment variables
	private $db_params;
	private $psa_params;
	private $install_params;
	private $db_links = array();

	// logging variables
	private $_logging = false;
	
	// debug variables
	private $_debug = false;
	private $_env_vars_backup = array();
	// update this array when you update the envvars in _prep_debug_data()
	private $_debug_env_vars = array(
		'BASE_URL_SCHEME',
		'BASE_URL_HOST',
		'BASE_URL_PORT',
		'BASE_URL_PATH',
		'WEB___DIR',
		'DB_main_TYPE',
		'DB_main_NAME',
		'DB_main_LOGIN',
		'DB_main_PASSWORD',
		'DB_main_HOST',
		'DB_main_VERSION',
		'DB_main_PORT',
		'DB_main_ADDRESS',
		'SETTINGS_admin_name',
		'SETTINGS_admin_password',
		'SETTINGS_title',
	);
	
	function __construct(){
	}
	
	public function set_debug($debug_mode){
		if(is_bool($debug_mode) && $debug_mode != $this->_debug){
			if($debug_mode == true){
				$this->_backup_env_vars();
				$this->_prep_debug_data();
				$this->_debug = true;
			}
			else{
				$this->_restore_env_vars();
				$this->_debug = false;
			}
		}
	}
	
	public function set_logging($logging_mode){
		if(is_bool($logging_mode) && $logging_mode != $this->_logging){
			if($logging_mode == true){
				$this->_logging = true;
			}
			else{
				$this->_logging = false;
			}
		}
	}
	
	/**
	 * This function prepares all the environment variables for the install, remove, upgrade, and configure actions
	 */
	private function _prep_configure_data(){
		$db_ids = array('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
		$this->db_params = $this->_get_db_env_vars($db_ids);
		$this->psa_params = $this->_get_psa_env_vars();
		$this->install_params = $this->_get_install_options_env_vars(); // These are the user entered options during install
	}
	
	/**
	 * This function prepares all the license data
	 */
	private function _prep_license_data(){
		$license_ids = array('main'); // This is set to 'main' because it is the hardcoded license id name in APP-META.XML
		$this->license_params = $this->_get_license_env_vars($license_ids);
	}
	
	public function install(){
		$this->_prep_configure_data();
		
		$successful_install = true;
		// FILES ARE AUTOMATICALLY COPIED OVER TO WEB ROOT
		
		if(!$this->_write_silent_install_config()){
			echo "Could not write silent install config\n";
			exit(1);
		}
		
		$this->_run_silent_install();
		
		if(!$this->_write_config_override()){
			$this->_drop_all_sugar_tables();
			echo "Could not write config override after installation\n";
			exit(1);
		}
		
		return $successful_install;
	}
	
	public function remove(){
		$this->_prep_configure_data();
		$this->_drop_all_sugar_tables();

		if($this->_debug){
			$this->_remove_config_files();
		}
	}
	
	public function upgrade(){
		$this->_prep_configure_data();
		
		
	}
	
	// TODO: Get the configuration working
	public function configure(){
		$this->_prep_configure_data();
		
		$this->_db_connect('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
		
		$user_name = mysql_real_escape_string($this->install_params['@@ADMIN_NAME@@'], $this->db_links['main']);
		$password = md5($this->install_params['@@ADMIN_PASSWORD@@']);
		
		mysql_query("update users set user_name = '{$user_name}', user_hash = '{$password}' where id = '1'", $this->db_links['main']);
		
		$this->_db_close_connection('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
		return;
	}
	
	public function install_license(){
		$this->_prep_configure_data();
	}
	
	public function remove_license(){
		$this->_prep_configure_data();
	}
	
	public function query_license(){
		$this->_prep_configure_data();
	}
	
	private function _write_silent_install_config(){
		
		// Only place the dynamic sugar_config_si values here. Static values go in the file, as included below
		//   this array
		$sugar_config_si = array(
			'setup_db_host_name' => $this->db_params['@@DB_MAIN_ADDRESS@@'], // Using address, not host. It includes the port
			'setup_db_sugarsales_user' => $this->db_params['@@DB_MAIN_LOGIN@@'], 
			'setup_db_sugarsales_password' => $this->db_params['@@DB_MAIN_PASSWORD@@'], 
			'setup_db_database_name' => $this->db_params['@@DB_MAIN_NAME@@'], 
			'setup_db_type' => $this->db_params['@@DB_MAIN_TYPE@@'], // Until we support other dbs, this is mysql
			'setup_db_admin_user_name' => $this->db_params['@@DB_MAIN_LOGIN@@'],
			'setup_db_admin_password' => $this->db_params['@@DB_MAIN_PASSWORD@@'],
			'setup_site_url' => $this->psa_params['@@ROOT_URL@@'],
			'setup_site_admin_user_name' => $this->install_params['@@ADMIN_NAME@@'],
			'setup_site_admin_password' => $this->install_params['@@ADMIN_PASSWORD@@'],
			'setup_license_key' => $this->install_params['@@SETUP_LICENSE_KEY@@'],
			'setup_system_name' => $this->install_params['@@TITLE@@'],
		);
		
		// Here's where we store the static config_si values. If you have any static values, place them there
		require('metadata/config_si_template.php');
		
		$config_si_path = $this->psa_params['@@ROOT_DIR@@']."/config_si.php";
		$success_config_write = $this->_write_array_to_file('sugar_config_si', $sugar_config_si, $config_si_path, true);
		
		return $success_config_write;
	}
	
	private function _run_silent_install(){
		$ch = curl_init();
		
		$url = $this->psa_params['@@ROOT_URL@@']."/install.php?goto=SilentInstall&cli=true";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$si_results = curl_exec ($ch);
		// If there was a curl error, we display and exit
		if($si_results === false){
			echo 'Curl error: ' . curl_error($ch);
			curl_close ($ch);
			exit(1);
		}
		curl_close ($ch);

		// message in a bottle
		preg_match( '/<bottle>(.*)<\/bottle>/s', $si_results, $message );
		if( count( $message ) == 2 ){
			// success
			// print( $message[1] );
		}
		else {
			// This means we have a sugar related error. Look for the message and output it
			preg_match( '/Exit (.*)/', $si_results, $message );

			// check for sugar install errors
			if( count( $message ) == 2 ){
				print( "Error.  Most likely your configuration file is invalid.  Message returned was:\n" );
			}
			else {
				print( "Unknown error.  I don't know about this type of error message:\n" );
			}
			print( $si_results . "\n" );
			exit( 1 );
		}
	}
	
	/**
	 * This is used to set some of the config override values after installation.
	 * * Original purpose for this function is to set default permissions for dirs and files
	 * * post installation since the webhosts don't always set the correct group and users
	 * * for the created files
	 */
	private function _write_config_override(){
		$config_override_path = $this->psa_params['@@ROOT_DIR@@']."/config_override.php";
		$sugar_config = array();
		
		// Check if there is already a config_override, and make sure to include it so we don't lose
		//   any previously set values
		if(file_exists($config_override_path)){
			require($config_override_path);
		}
		
		// Add any parameters we want to set in the config_override in this file
		require('metadata/config_override_template.php');
		
		$success_write = $this->_write_array_to_file('sugar_config', $sugar_config, $config_override_path, false);
		
		return $success_write;
	}
	
	private function _drop_all_sugar_tables(){
		$this->_db_connect('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
		
		$sugar_tables = $this->_get_db_tables('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
		
		foreach($sugar_tables as $table_name){
			mysql_query("DROP TABLE {$table_name}", $this->db_links['main']);
		}
		
		$this->_db_close_connection('main'); // This is set to 'main' because it is the hardcoded db name in APP-META.XML
	}
	
	private function _db_connect($db_id){
		$db_address = $this->db_params["@@DB_".strtoupper($db_id)."_ADDRESS@@"];
		$db_login = $this->db_params["@@DB_".strtoupper($db_id)."_LOGIN@@"];
		$db_password = $this->db_params["@@DB_".strtoupper($db_id)."_PASSWORD@@"];
		$db_name = $this->db_params["@@DB_".strtoupper($db_id)."_NAME@@"];
		
		$this->db_links[$db_id] = mysql_connect($db_address, $db_login, $db_password);
		
		if(!$this->db_links[$db_id]){
			print "Unable to connect to DB: " . mysql_errno() . ": " . mysql_error() . "\n";
			exit(1);
		}
		
		if(!mysql_select_db($db_name)){
			print "Unable to select $db_name database: " . mysql_errno() . ": " . mysql_error() . "\n";
			exit(1);
		}
	}
	
	private function _db_close_connection($db_id){
		if(isset($this->db_links[$db_id]) && $this->db_links[$db_id]){
			mysql_close($this->db_links[$db_id]);
			unset($this->db_links[$db_id]);
		}
	}
	
	private function _get_db_tables($db_id){
		$tables = array();

		$query = "SHOW TABLES";
		$res = mysql_query($query, $this->db_links[$db_id]);
		while($row = mysql_fetch_row($res)){
			$tables[] = $row[0];
		}
		
		return $tables;
	}
	
	/**
	 * This function retrieves the environment variable that is set on the host that is handling
	 *   the sugar package
	 */
	private function _fetch_env_var($envvar){
		$res = getenv($envvar);
		if ($res === false)
			return NULL;
		return $res;
	}
	
	/**
	 * This function retrieves all the options that the user is prompted to fill
	 * When that list is updated in APP-META.xml, the setting_options array below
	 *   needs to be updated as well
	 * 
	 * Note: You will need to update the installer and the configure functionality
	 *   when you update this
	 */
	private function _get_install_options_env_vars(){
		$setting_options = array(
			'admin_name',
			'admin_password',
			'title',
			'setup_license_key',
		);
		
		$parameters = $this->_get_settings_env_vars($setting_options);
		return $parameters;
	}
	
	/**
	 * Returns: array containing path information
	 * @@BASE_URL_SCHEME@@	  - http or https
	 * @@BASE_URL_HOST@@	  - example.com
	 * @@BASE_URL_PORT@@	  - alternate port or blank if port 80
	 * @@BASE_URL_PATH@@	  - sugar instance path in url
	 * @@SSL_ENABLED@@		  - 1 if true, 0 if false
	 * @@SSL_ENABLED_YN@@	  - y if yes, n if no
	 * @@INSTALL_PREFIX_WLF@@ - BASE_URL_PATH without the trailing slash
	 * @@ROOT_URL@@			  - the full url - http://example.com/instance (without a trailing slash)
	 * @@ROOT_DIR@@			  - the full path on host - /var/www/example.com/instance (without a trailing slash)
	 */
	private function _get_psa_env_vars(){
		$scheme = $this->_fetch_env_var("BASE_URL_SCHEME"); // http or https
		$host = $this->_fetch_env_var("BASE_URL_HOST"); // example.com
		$port = $this->_fetch_env_var("BASE_URL_PORT"); // alternate port or blank if port 80
		$path = $this->_fetch_env_var("BASE_URL_PATH"); // sugar instance path in url
	
		$full = $scheme . "://" . $host . ($port !== NULL ? ":$port" : "") . ($path[0] == "/" ? "" : "/") . $path;
	
		$parameters = array();
		$parameters["@@"."BASE_URL_SCHEME"."@@"] = $scheme;
		if($scheme == 'http'){
			$parameters["@@"."SSL_ENABLED"."@@"] = 0;
			$parameters["@@"."SSL_ENABLED_YN"."@@"] = 'n';
		}
		else if($scheme == 'https'){
			$parameters["@@"."SSL_ENABLED"."@@"] = 1;
			$parameters["@@"."SSL_ENABLED_YN"."@@"] = 'y';
		}
		$parameters["@@"."BASE_URL_HOST"."@@"] = $host;
		$parameters["@@"."BASE_URL_PORT"."@@"] = $port;
	
		$my_url_path = $path;
		$my_urlwls_path = $path;
		if($my_url_path == "/"){
			$my_url_path = ".";
			$my_urlwls_path = $my_url_path;
		}
		else if($my_url_path[strlen($my_url_path)-1] == "/"){
			$my_url_path = substr($my_url_path, 0, strlen($my_url_path)-1);
			$my_urlwls_path = "/".$my_url_path;
		}
		$parameters["@@"."BASE_URL_PATH"."@@"] = $my_url_path;
		$parameters["@@"."INSTALL_PREFIX_WLS"."@@"] = $my_urlwls_path;
	
		$my_root_url = $full;
		if($my_root_url[strlen($my_root_url)-1] == "/"){
		$my_root_url = substr($my_root_url, 0, strlen($my_root_url)-1);
		}
		$parameters["@@"."ROOT_URL"."@@"] = $my_root_url;
	
		$my_web_dir = $this->_fetch_env_var("WEB___DIR");
		while($my_web_dir[strlen($my_web_dir)-1] == "/"){
			$my_web_dir = substr($my_web_dir, 0, strlen($my_web_dir)-1);
		}
		$parameters["@@"."ROOT_DIR"."@@"] = $my_web_dir;
	
		return $parameters;
	}
	
	/**
	 * This function gets all the database information as set by the host
	 *
	 * @@DB_".strtoupper($db_id)."_TYPE@@		- The database type (mysql, mssql, etc)
	 * @@DB_".strtoupper($db_id)."_NAME@@		- The database name
	 * @@DB_".strtoupper($db_id)."_LOGIN@@		- The database user login
	 * @@DB_".strtoupper($db_id)."_PASSWORD@@	- The database user password
	 * @@DB_".strtoupper($db_id)."_HOST@@		- The database host machine
	 * @@DB_".strtoupper($db_id)."_VERSION@@	- The database version
	 * @@DB_".strtoupper($db_id)."_PORT@@		- The port number used
	 * @@DB_".strtoupper($db_id)."_PREFIX@@		- The prefix (not supported on sugar)
	 * @@DB_".strtoupper($db_id)."_ADDRESS@@	- The database address, which is the host followed by the port
	 */
	private function _get_db_env_vars($db_ids){
		$parameters = array();
		
		foreach($db_ids as $db_id) {
			$parameters["@@"."DB_".strtoupper($db_id)."_TYPE"."@@"] = $this->_fetch_env_var("DB_{$db_id}_TYPE");
			$parameters["@@"."DB_".strtoupper($db_id)."_NAME"."@@"] = $this->_fetch_env_var("DB_{$db_id}_NAME");
			$parameters["@@"."DB_".strtoupper($db_id)."_LOGIN"."@@"] = $this->_fetch_env_var("DB_{$db_id}_LOGIN");
			$parameters["@@"."DB_".strtoupper($db_id)."_PASSWORD"."@@"] = $this->_fetch_env_var("DB_{$db_id}_PASSWORD");
			$parameters["@@"."DB_".strtoupper($db_id)."_HOST"."@@"] = $this->_fetch_env_var("DB_{$db_id}_HOST");
			$parameters["@@"."DB_".strtoupper($db_id)."_VERSION"."@@"] = $this->_fetch_env_var("DB_{$db_id}_VERSION");
			$parameters["@@"."DB_".strtoupper($db_id)."_PORT"."@@"] = $this->_fetch_env_var("DB_{$db_id}_PORT");
			$parameters["@@"."DB_".strtoupper($db_id)."_PREFIX"."@@"] = $this->_get_db_prefix($db_id);
			$parameters["@@"."DB_".strtoupper($db_id)."_ADDRESS"."@@"] = $this->_get_db_address($db_id);
		}
	
		return $parameters;
	}
	
	/**
	 * This function gets all the license information as set by the host
	 * 
	 */
	private function _get_license_env_vars($license_ids){
		$parameters = array();
		foreach($license_ids as $license_id) {
			$parameters["@@"."LICENSE_".strtoupper($license_id)."_FILE"."@@"] = $this->_fetch_env_var("LICENSE_{$license_id}_FILE");
		}
	
		return $parameters;
	}
	
	private function _get_db_prefix($db_id){
		if($this->_fetch_env_var("DB_{$db_id}_PREFIX") !== false){
			return $this->_fetch_env_var("DB_{$db_id}_PREFIX");
		} else{
			return '';
		}
	}
	
	private function _get_db_address($db_id){
		$db_address = $this->_fetch_env_var("DB_{$db_id}_HOST");
		$db_port = $this->_fetch_env_var("DB_{$db_id}_PORT");
		if(!empty($db_port))
			$db_address .= ':' . $db_port;
		
		return $db_address;
	}
	
	private function _get_web_dir($web_id){
		$web_id_parameter = str_replace("/", "_", $web_id);
		return $this->_fetch_env_var("WEB_${web_id_parameter}_DIR");
	}
	
	private function _get_web_env_vars($web_ids){
		$parameters = array();
		foreach($web_ids as $web_id) {
			$web_id_parameter = str_replace("/", "_", $web_id);
			$parameters["@@".strtoupper($web_id)."_DIR"."@@"] = $this->_fetch_env_var("WEB_${web_id_parameter}_DIR");
		}
	
		return $parameters;
	}
	
	private function _get_settings_env_vars($params){
		$parameters = array();
		foreach($params as $param) {
			$parameters["@@".strtoupper($param)."@@"] = $this->_fetch_env_var("SETTINGS_{$param}");
		}
	
		return $parameters;
	}
	
	private function _get_settings_old_env_vars($params){
		$parameters = array();
		foreach($params as $param) {
			$parameters["@@OLDSETTINGS_".strtoupper($param)."@@"] = $this->_fetch_env_var("OLDSETTINGS_{$param}");
		}
	
		return $parameters;
	}
	
	private function _get_settings_enum_env_vars($enum_params){
		$parameters = array();
		foreach($enum_params as $param_id => $elements_ids_map) {
			$param_value = $this->_fetch_env_var("SETTINGS_${param_id}");
			foreach($elements_ids_map as $element_id => $value_for_app){
				if($element_id == $param_value){
					$parameters["@@".strtoupper($param_id)."@@"] = $value_for_app;
				}
			}
		}
	
		return $parameters;
	}
	
	private function _get_crypt_settings_env_vars($crypt_params){
		$parameters = array();
		foreach($crypt_params as $param) {
			$fname = "{$param}_crypt";
			$parameters["@@".strtoupper($param)."@@"] = $fname($this->_fetch_env_var("SETTINGS_{$param}"));
		}
	
		return $parameters;
	}
	
	/**
	 * the_name			- the name of the array - ex: $my_array
	 * the_array		- the actual array that is to be written
	 * the_file			- the file path that contains the array
	 * write_full_array	- write a full array or single elements
	 *	if true:  $arr = array('1' => '2', '3' => '4');
	 *	if false: $arr['1'] = '2'; $arr['3'] = '4';
	 */
	private function _write_array_to_file( $the_name, $the_array, $the_file, $write_full_array = true){
		$the_string =   "<?php\n" .
					'// created: ' . date('Y-m-d H:i:s') . "\n\n";
		
		if($write_full_array){
			$the_string .= $this->_get_array_string_full($the_name, $the_array);
		}
		else{
			$the_string .= $this->_get_array_string_single_elements($the_name, $the_array);
		}
		
		if($fh = fopen($the_file, 'w')){
			fputs($fh, $the_string);
			fclose($fh);
			return true;
		}
		else{
			return false;
		}
	}
	
	private function _get_array_string_full($the_name, $the_array){
		$the_string =  "\$$the_name = " .
						var_export( $the_array, true ) .
						";\n";
		
		return $the_string;
	}
	
	private function _get_array_string_single_elements($the_name, $the_array, $previous_indexes = array()){
		$the_string = '';

		foreach($the_array as $index => $element){
			if(!is_array($element)){
				$the_string .= "\${$the_name}";
				foreach($previous_indexes as $previous_index){
					$the_string .= "['{$previous_index}']";
				}
				$the_string .= "['{$index}'] = ";
				if(is_float($element) || is_int($element)){
					$the_string .= $element;
				}
				else if(is_bool($element)){
					$the_string .= ($element === true ? 'true' : 'false');
				}
				else{
					$the_string .= "'{$element}'";
				}
				$the_string .= ";\n";
			}
			else{
				array_push($previous_indexes, $index);
				$the_string .= $this->_get_array_string_single_elements($the_name, $element, $previous_indexes);
				array_pop($previous_indexes);
			}
		}

		return $the_string;
	}
	
	
	/**
	 * Originally created for debug mode, remove the config files created so we can reinstall
	 */
	private function _remove_config_files(){
		$config_files = array(
			'config.php',
			'config_si.php',
			'config_override.php',
		);
		
		foreach($config_files as $config){
			unlink($this->psa_params['@@ROOT_DIR@@']."/".$config);
		}
	}
	
	private function _backup_env_vars(){
		foreach($this->_debug_env_vars as $env_var){
			$value = getenv($env_var);
			$this->_env_vars_backup[$env_var] = $value;
		}
	}
	
	private function _restore_env_vars(){
		foreach($this->_env_vars_backup as $env_key => $env_val){
			$env_string = $env_key;
			if($env_val !== false){
				$env_string .= "={$env_val}";
			}
			putenv($env_string);
		}
	}
	
	/**
	 * Set fake data, and debug info for development purposes. $this->_debug should be false before submitting package
	 */
	private function _prep_debug_data(){
		$host="localhost";
		$port="3306";
		$login = "root";
		$pass = "root";
		$link = mysql_connect($host, $login, $pass);
		if(!$link){
			echo "Could not connect to localhost in debug mode. Exiting\n";
			exit(1);
		}
		$res = mysql_query("select version() as v");
		$row = mysql_fetch_assoc($res);
		$version = $row['v'];
		mysql_close($link);
		
		// update the _debug_env_vars array when you update the function calls below
		putenv("BASE_URL_SCHEME=http");
		putenv("BASE_URL_HOST=localhost");
		putenv("BASE_URL_PORT=80");
		putenv("BASE_URL_PATH=aps_sugarcrm");
		putenv("WEB___DIR=/Applications/MAMP/htdocs/aps_sugarcrm");
		
		putenv("DB_main_TYPE=mysql");
		putenv("DB_main_NAME=aps_sugarcrm");
		putenv("DB_main_LOGIN={$login}");
		putenv("DB_main_PASSWORD={$pass}");
		putenv("DB_main_HOST={$host}");
		putenv("DB_main_VERSION={$version}");
		putenv("DB_main_PORT={$port}");
		putenv("DB_main_ADDRESS={$host}:{$port}");
		
		putenv("SETTINGS_admin_name=admin");
		putenv("SETTINGS_admin_password=asdf");
		putenv("SETTINGS_title=SugarCRM APS DEBUG");
	}
	
	private function _log_this($file, $log){
		if($this->_logging == true){
			$fp = fopen($this->psa_params['@@ROOT_DIR@@']."/".$file, 'a');
			fwrite($fp, "[".date("Y-m-d H:i:s")."] ".$log."\n");
			fclose($fp);
		}
	}
}

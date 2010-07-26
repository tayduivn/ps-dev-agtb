<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: install_utils.php,v 1.38 2006/03/22 20:21:03 chris Exp
 * $Description: TODO: To be written. Portions created by SugarCRM are Copyright
 * (C) SugarCRM, Inc. All Rights Reserved. Contributor(s):
 * ______________________________________..
 * *******************************************************************************/

require_once('include/utils/zip_utils.php');
require_once('include/utils/file_utils.php');
require_once('include/upload_file.php');
require_once('include/dir_inc.php');

///////////////////////////////////////////////////////////////////////////////
////	FROM welcome.php
/**
 * returns lowercase lang encoding
 * @return string	encoding or blank on false
 */
function parseAcceptLanguage() {
	$lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(strpos($lang, ';')) {
		$exLang = explode(';', $lang);
		return strtolower(str_replace('-','_',$exLang[0]));
	} else {
		$match = array();
		if(preg_match("#\w{2}\-?\_?\w{2}#", $lang, $match)) {
			return strtolower(str_replace('-','_',$match[0]));
		}
	}
	return '';	
}


///////////////////////////////////////////////////////////////////////////////
////	FROM performSetup.php
/**
 * creates the Sugar DB user (if not admin)
 */
function handleDbCreateSugarUser() {
	global $mod_strings;
	global $setup_db_database_name;
	global $setup_db_host_name;
	global $setup_db_admin_user_name;
	global $setup_db_admin_password;
	global $sugar_config;
	global $setup_db_sugarsales_user;
	global $setup_site_host_name;
	global $setup_db_sugarsales_password;

	echo $mod_strings['LBL_PERFORM_CREATE_DB_USER'];
	
	switch($_SESSION['setup_db_type']) {
		case "mysql":
			$link	= @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
			$query	= "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
						ON `{$setup_db_database_name}`.*
						TO \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\"
						IDENTIFIED BY '{$setup_db_sugarsales_password}';";
		
			if(!@mysql_query($query, $link)) {
				$errno = mysql_errno();
				$error = mysql_error();
			}
		
			$query	= "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"{$setup_site_host_name}\" = old_password('{$setup_db_sugarsales_password}');";
		
			if(!@mysql_query($query, $link)) {
				 $errno = mysql_errno();
				 $error = mysql_error();
			}
		
			if($setup_site_host_name != 'localhost') {
				echo $mod_strings['LBL_PERFORM_CREATE_LOCALHOST'];
				
				$link	= @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
				$query	= "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
							ON `{$setup_db_database_name}`.*
							TO \"{$setup_db_sugarsales_user}\"@\"localhost\"
							IDENTIFIED BY '{$setup_db_sugarsales_password}';";
				if(!@mysql_query($query, $link)) {
				 	$errno = mysql_errno();
				 	$error = mysql_error();
				}
		
				$query = "SET PASSWORD FOR \"{$setup_db_sugarsales_user}\"@\"localhost\"\ = old_password('{$setup_db_sugarsales_password}');";
		
				if(!@mysql_query($query, $link)) {
				 	$errno = mysql_errno();
				 	$error = mysql_error();
				}
			} // end LOCALHOST
		
			mysql_close($link);
		break;
		
		case 'mssql':
			$link = mssql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);	
			$query = "USE " . $setup_db_database_name . ";";
			@mssql_query($query);
			
			$query = "EXEC sp_addlogin '$setup_db_sugarsales_user', '$setup_db_sugarsales_password', '$setup_db_database_name', 'english'";
			if(!@mssql_query($query)) {
				$errno = 9999;
				$error = "Error Adding Login. SQL Query: $query";
			}
			
			$query = "EXEC sp_grantdbaccess '$setup_db_sugarsales_user'";
			if(!@mssql_query($query)) {
				$errno = 9999;
				$error = "Error Granting Access. SQL Query: $query";
			}
			
			$query = "EXEC sp_addRoleMember 'db_datareader','$setup_db_sugarsales_user'";
			if(!@mssql_query($query)) {
				$errno = 9999;
				$error = "Error Adding Role db_datareader. SQL Query: $query";
			}
			
			$query = "EXEC sp_addRoleMember 'db_datawriter','$setup_db_sugarsales_user'";
			if(!@mssql_query($query)) {
				$errno = 9999;
				$error = "Error Adding Role db_datawriter. SQL Query: $query";
			}
		break;
	} // end switch()
	echo $mod_strings['LBL_PERFORM_DONE'];
}

/**
 * creates the new database
 */
function handleDbCreateDatabase() {
	global $mod_strings;
	global $setup_db_database_name;
	global $setup_db_host_name;
	global $setup_db_admin_user_name;
	global $setup_db_admin_password;
	global $sugar_config;
	
	echo "{$mod_strings['LBL_PERFORM_CREATE_DB_1']} {$setup_db_database_name} {$mod_strings['LBL_PERFORM_CREATE_DB_2']} {$setup_db_host_name}...";

	switch($_SESSION['setup_db_type']) {
		case 'mysql':
			$link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
			$query = 'CREATE DATABASE `' . $setup_db_database_name . '` CHARACTER SET utf8 COLLATE utf8_general_ci';
			@mysql_query($query, $link);
			mysql_close($link);
		break;
	
		case 'mssql': 
			$link = @mssql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);		
			$setup_db_database_name = str_replace(' ', '_', $setup_db_database_name);  // remove space
			$query = 'create database '.$setup_db_database_name;	
			mssql_query($query);
			mssql_close($link);				 
		break;			
	
	}     
	echo $mod_strings['LBL_PERFORM_DONE'];
}


/**
 * handles creation of Log4PHP properties file
 */
function handleLog4Php() {
	global $setup_site_log_dir;
	global $setup_site_log_file;
	
	if(is_writable("log4php.properties") && ($fh = @ fopen("log4php.properties", "r+"))) {
		$props = fread($fh, filesize("log4php.properties"));
		$props = preg_replace('/(log4php.appender.A2.File=).*\n/', "$1" . $setup_site_log_dir . "/" . $setup_site_log_file . "\n", $props);
		rewind( $fh );
		fwrite( $fh, $props );
		ftruncate( $fh, ftell($fh) );
		fclose( $fh );
	}
}


/**
 * takes session vars and creates config.php
 * @return array bottle collection of error messages
 */
function handleSugarConfig() {
	global $bottle;
	global $cache_dir;
	global $mod_strings;
	global $setup_site_host_name;
	global $setup_site_log_dir;
	global $setup_site_log_file;
	global $setup_site_session_path;
	global $setup_site_guid;
	global $setup_site_url;
    global $setup_site_portal_username;
    global $setup_site_portal_password;
    global $parent_setup_site_url;
	global $setup_sugar_version;
	global $sugar_config;
	
	echo "<b>{$mod_strings['LBL_PERFORM_CONFIG_PHP']} (config.php)</b><br>";
	///////////////////////////////////////////////////////////////////////////////
	////	$sugar_config SETTINGS
	if( is_file('config.php') ){
		$is_writable = is_writable('config.php');
		// require is needed here (config.php is sometimes require'd from install.php)
		require('config.php');
	} else {
		$is_writable = is_writable('.');
	}
	
	// build default sugar_config and merge with new values
	$sugar_config = sugarArrayMerge(get_sugar_config_defaults(), $sugar_config);
	// always lock the installer
	$sugar_config['installer_locked'] = true;
	// we're setting these since the user was given a fair chance to change them
	$sugar_config['cache_dir']						= $cache_dir;
	$sugar_config['default_charset']				= $mod_strings['DEFAULT_CHARSET'];
	$sugar_config['default_email_client']			= 'sugar';
	$sugar_config['default_email_editor']			= 'html';
	$sugar_config['host_name']						= $setup_site_host_name;
	$sugar_config['import_dir']					= $cache_dir.'import/';
	$sugar_config['js_custom_version']				= '';
	$sugar_config['log_dir']						= $setup_site_log_dir;
	$sugar_config['log_file']						= $setup_site_log_file;
	$sugar_config['session_dir']					= $setup_site_session_path;
	$sugar_config['site_url']						= $setup_site_url;
    $sugar_config['parent_site_url']                = $parent_setup_site_url;
    $sugar_config['portal_username']                = $setup_site_portal_username;
    $sugar_config['portal_password']                = $setup_site_portal_password;
	$sugar_config['sugar_version']					= $setup_sugar_version;
	$sugar_config['tmp_dir']						= $cache_dir.'xml/';
	$sugar_config['upload_dir']					    = $cache_dir.'upload/';
	$sugar_config['use_php_code_json']				= returnPhpJsonStatus(); // true on error
    $sugar_config['authenticationClass']            = 'PortalAuthenticate';
	
	
	// use MB-demo data?
	if(isset($_SESSION['setup_db_use_mb_demo_data']) && $_SESSION['setup_db_use_mb_demo_data'] == true) {
		$sugar_config['i18n_test'] = true;
	} else {
        $sugar_config['i18n_test'] = false;
    }
	if( isset($_SESSION['setup_site_sugarbeet']) ) {
		$sugar_config['sugarbeet'] = $_SESSION['setup_site_sugarbeet'];
	}
	if( !isset( $sugar_config['unique_key'] ) ){
		$sugar_config['unique_key'] = $setup_site_guid;
	}
	// add installed langs to config
	// entry in upgrade_history comes AFTER table creation
	if(isset($_SESSION['INSTALLED_LANG_PACKS']) && is_array($_SESSION['INSTALLED_LANG_PACKS']) && !empty($_SESSION['INSTALLED_LANG_PACKS'])) {
		foreach($_SESSION['INSTALLED_LANG_PACKS'] as $langZip) {
			$lang = getSugarConfigLanguageArray($langZip);
			if(!empty($lang)) {
				$exLang = explode('::', $lang);
				if(is_array($exLang) && count($exLang) == 3) {
					$sugar_config['languages'][$exLang[0]] = $exLang[1];
				} 
			}
		}
	}
	
	ksort($sugar_config);
	$sugar_config_string = "<?php\n" .
		'// created: ' . date('Y-m-d H:i:s') . "\n" .
		'$sugar_config = ' .
		var_export($sugar_config, true) .
		";\n?>\n";
	if($is_writable && write_array_to_file( "sugar_config", $sugar_config, "config.php")) {
		// was 'Done'
	} else {
		echo 'failed<br>';
		echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_1']}</p>\n";
		echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_2']}</p>\n";
		echo "<TEXTAREA  rows=\"15\" cols=\"80\">".$sugar_config_string."</TEXTAREA>";
		echo "<p>{$mod_strings['ERR_PERFORM_CONFIG_PHP_3']}</p>";
	
		$bottle[] = $mod_strings['ERR_PERFORM_CONFIG_PHP_4'];
	}
	
	////	END $sugar_config
	///////////////////////////////////////////////////////////////////////////////
	return $bottle;	
}
/**
 * (re)write the .htaccess file to prevent browser access to the log file
 */
function handleHtaccess() {
	global $mod_strings;
	global $parsed_url;
	global $setup_site_log_dir;
	global $setup_site_log_file;
	global $setup_site_url;
	
	$htaccess_failed= false;
	$htaccess_file	 = ".htaccess";
	$site_path		 = $parsed_url['path'];
	$redirect_str	 = "# BEGIN SUGARCRM RESTRICTIONS\n";
	$redirect_str	.= "RedirectMatch $site_path/$setup_site_log_dir/$setup_site_log_file.* $setup_site_url/log_file_restricted.html\n";
	$redirect_str	.= "RedirectMatch $site_path/$setup_site_log_dir/emailman.log $setup_site_url/log_file_restricted.html\n";
	$redirect_str	.= "RedirectMatch $site_path/not_imported_(.*).txt $setup_site_url/log_file_restricted.html\n";
	$redirect_str	.= "RedirectMatch $site_path/XTemplate/(.*)/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/data/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/examples/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/include/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/include/(.*)/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/log4php/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/log4php/(.*)/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/metadata/(.*)/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/modules/(.*)/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/soap/(.*).php $setup_site_url/index.php\n";
	$redirect_str	.= "RedirectMatch $site_path/emailmandelivery.php $setup_site_url/index.php\n";
	$redirect_str	.= "# END SUGARCRM RESTRICTIONS\n";
	$redirect_str	= preg_replace( "#/./#", "/", $redirect_str );
	
	if( file_exists( $htaccess_file ) && (filesize( $htaccess_file ) > 0) ) {
		if( is_writable( $htaccess_file ) && ($fh = @ fopen( $htaccess_file, "r+" )) ) {
			$props  = fread( $fh, filesize( $htaccess_file ) );
	
			if( !preg_match( "=" . $redirect_str . "=", $props ) ) {
				// need to add redirect
				$props .= $redirect_str;
			}
	
			rewind( $fh );
			fwrite( $fh, $props );
			ftruncate( $fh, ftell($fh) );
			fclose( $fh );
		} else {
			$htaccess_failed = true;
		}
	} else {
		// create the file
		if( $fh = @ fopen( $htaccess_file, "w") ) {
			fputs( $fh, $redirect_str, strlen($redirect_str) );
			fclose( $fh );
		} else {
			$htaccess_failed = true;
		}
	}
	if( $htaccess_failed ) {
		echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_1']}<span class=stop>{$htaccess_file}</span> {$mod_strings['ERR_PERFORM_HTACCESS_2']}</p>\n";
		echo "<p>{$mod_strings['ERR_PERFORM_HTACCESS_3']}</p>\n";
		echo $redirect_str;
	}
}

/**
 * Drop old tables if table exists and told to drop it
 */
function drop_table_install( &$focus ){
    global $db;
    global $dictionary;

    $result = $db->tableExists($focus->table_name);

    if( $result ){
        $focus->drop_tables();
        $GLOBALS['log']->info("Dropped old ".$focus->table_name." table.");
        return 1;
    }
    else {
        $GLOBALS['log']->info("Did not need to drop old ".$focus->table_name." table.  It doesn't exist.");
        return 0;
    }
}

// Creating new tables if they don't exist.
function create_table_if_not_exist( &$focus ){
    global  $db;
    $table_created = false;

    // normal code follows
    $result = $db->tableExists($focus->table_name);
    if($result){
        $GLOBALS['log']->info("Table ".$focus->table_name." already exists.");
    } else {
        $focus->create_tables();
        $GLOBALS['log']->info("Created ".$focus->table_name." table.");
        $table_created = true;
    }
    return $table_created;
}

function create_default_users(){
    global  $db;
    global $setup_site_admin_password;
    global $create_default_user;
    global $sugar_config;

    //Create default admin user
    $user = new User();
    $user->id = 1;
    $user->new_with_id = true;
    $user->last_name = 'Administrator';
    $user->user_name = 'admin';
    $user->title = "Administrator";
    $user->status = 'Active';
    $user->is_admin = true;
    $user->user_password = $user->encrypt_password($setup_site_admin_password);
    $user->user_hash = strtolower(md5($setup_site_admin_password));
    $user->email = '';
    $user->save();

    // echo 'Creating RSS Feeds';
    $feed = new Feed();
    $feed->createRSSHomePage($user->id);


    // We need to change the admin user to a fixed id of 1.
    // $query = "update users set id='1' where user_name='$user->user_name'";
    // $result = $db->query($query, true, "Error updating admin user ID: ");

    $GLOBALS['log']->info("Created ".$user->table_name." table. for user $user->id");

    if( $create_default_user ){
        $default_user = new User();
        $default_user->last_name = $sugar_config['default_user_name'];
        $default_user->user_name = $sugar_config['default_user_name'];
        $default_user->status = 'Active';
        if( isset($sugar_config['default_user_is_admin']) && $sugar_config['default_user_is_admin'] ){
            $default_user->is_admin = true;
        }
        $default_user->user_password = $default_user->encrypt_password($sugar_config['default_password']);
        $default_user->user_hash = strtolower(md5($sugar_config['default_password']));
        $default_user->save();
        $feed->createRSSHomePage($user->id);
    }
}

function set_admin_password( $password ) {
    global $db;

    $user = new User();
    $encrypted_password = $user->encrypt_password($password);
    $user_hash = strtolower(md5($password));

    $query = "update users set user_password='$encrypted_password', user_hash='$user_hash' where id='1'";
    
    $db->query($query);
}

function insert_default_settings(){
    global $db;
    global $setup_sugar_version;
    global $sugar_db_version;


    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'fromaddress', 'do_not_reply@example.com')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'fromname', 'SugarCRM')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'send_by_default', '1')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'on', '0')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('notify', 'send_from_assigning_user', '0')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpserver', 'localhost')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpport', '25')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'sendtype', 'sendmail')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpuser', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtppass', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('mail', 'smtpauth_req', '0')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('info', 'sugar_version', '" . $sugar_db_version . "')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('MySettings', 'tab', '')");
    $db->query("INSERT INTO config (category, name, value) VALUES ('portal', 'on', '0')");

    //BEGIN SUGARCRM flav=pro ONLY 
    // license info
    $db->query( "INSERT INTO config (category, name, value) VALUES ( 'license', 'users',        '0' )" );
    $db->query( "INSERT INTO config (category, name, value) VALUES ( 'license', 'expire_date',  '' )" );
    $db->query( "INSERT INTO config (category, name, value) VALUES ( 'license', 'key',          '' )" );
    //END SUGARCRM flav=pro ONLY 

	//BEGIN SUGARCRM flav=pro ONLY 
    //systems data
 	global $sugar_config;
 	require_once('modules/Administration/System.php');
	$system = new System();
    $system->system_key = $sugar_config['unique_key'];
    $system->user_id = 1;
    $system->last_connect_date = gmdate("Y-m-d H:i:s");
	$system_id = $system->retrieveNextKey();
	$db->query( "INSERT INTO config (category, name, value) VALUES ( 'system', 'system_id', '" . $system_id . "')" );
    //END SUGARCRM flav=pro ONLY 
}

// BEGIN SUGARCRM flav=pro ONLY 
function update_license_settings( $users, $expire_date, $key, $num_lic_oc ){
    global $db;

    $query = "DELETE FROM config WHERE category='license' AND name='users'";
    $db->query($query);
    $query = "INSERT INTO config (value, category, name) VALUES ('$users', 'license', 'users')";
    $db->query($query);

    $query = "DELETE FROM config WHERE category='license' AND name='expire_date'";
    $db->query($query);
    $query = "INSERT INTO config (value, category, name) VALUES ('$expire_date', 'license', 'expire_date')";
    $db->query($query);

    $query = "DELETE FROM config WHERE category='license' AND name='key'";
    $db->query($query);
    $query = "INSERT INTO config (value, category, name) VALUES ('$key', 'license', 'key')";
    $db->query($query);

    $query = "DELETE FROM config WHERE category='license' AND name='num_lic_oc'";
    $db->query($query);
    $query = "INSERT INTO config (value, category, name) VALUES ('$num_lic_oc', 'license', 'num_lic_oc')";
    $db->query($query);
}
// END SUGARCRM flav=pro ONLY 





// Returns true if the given file/dir has been made writable (or is already
// writable).
function make_writable($file)
{
	$ret_val = false;
	if(is_file($file) || is_dir($file))
	{
		if(is_writable($file))
		{
			$ret_val = true;
		}
		else
		{
			$original_fileperms = fileperms($file);

			// add user writable permission
			$new_fileperms = $original_fileperms | 0x0080;
			@chmod($file, $new_fileperms);

			if(is_writable($file))
			{
				$ret_val = true;
			}
			else
			{
				// add group writable permission
				$new_fileperms = $original_fileperms | 0x0010;
				@chmod($file, $new_fileperms);

				if(is_writable($file))
				{
					$ret_val = true;
				}
				else
				{
					// add world writable permission
					$new_fileperms = $original_fileperms | 0x0002;
					@chmod($file, $new_fileperms);

					if(is_writable($file))
					{
						$ret_val = true;
					}
				}
			}
		}
	}

	return $ret_val;
}

function recursive_make_writable($start_file)
{
	$ret_val = make_writable($start_file);

	if($ret_val && is_dir($start_file))
	{
		// PHP 4 alternative to scandir()
		$files = array();
		$dh = opendir($start_file);
		$filename = readdir($dh);
		while(!empty($filename))
		{
			if($filename != '.' && $filename != '..')
			{
				$files[] = $filename;
			}

			$filename = readdir($dh);
		}

		foreach($files as $file)
		{
			$ret_val = recursive_make_writable($start_file . '/' . $file);

			if(!$ret_val)
			{
				break;
			}
		}
	}

	return $ret_val;
}

function recursive_is_writable($start_file)
{
	$ret_val = is_writable($start_file);

	if($ret_val && is_dir($start_file))
	{
		// PHP 4 alternative to scandir()
		$files = array();
		$dh = opendir($start_file);
		$filename = readdir($dh);
		while(!empty($filename))
		{
			if($filename != '.' && $filename != '..')
			{
				$files[] = $filename;
			}

			$filename = readdir($dh);
		}

		foreach($files as $file)
		{
			$ret_val = recursive_is_writable($start_file . '/' . $file);

			if(!$ret_val)
			{
				break;
			}
		}
	}

	return $ret_val;
}




function getMysqlVersion($link) {
	if(is_resource($link)) {
		$version = mysql_get_server_info($link);
		return preg_replace('/[A-Za-z\-]/','',$version);
	}
	return 0;
}




// one place for form validation/conversion to boolean
function get_boolean_from_request( $field ){
    if( !isset($_REQUEST[$field]) ){
        return( false );
    }

    if( ($_REQUEST[$field] == 'on') || ($_REQUEST[$field] == 'yes') ){
        return(true);
    }
    else {
        return(false);
    }
}

function stripslashes_checkstrings($value){
   if(is_string($value)){
      return stripslashes($value);
   }
   return $value;
}


function print_debug_array( $name, $debug_array ){
    ksort( $debug_array );

    print( "$name vars:\n" );
    print( "(\n" );

    foreach( $debug_array as $key => $value ){
        if( stristr( $key, "password" ) ){
            $value = "WAS SET";
        }
        print( "    [$key] => $value\n" );
    }

    print( ")\n" );
}

function print_debug_comment(){
    if( !empty($_REQUEST['debug']) ){
        $_SESSION['debug'] = $_REQUEST['debug'];
    }

    if( !empty($_SESSION['debug']) && ($_SESSION['debug'] == 'true') ){
        print( "<!-- debug is on (to turn off, hit any page with 'debug=false' as a URL parameter.\n" );

        print_debug_array( "Session",   $_SESSION );
        print_debug_array( "Request",   $_REQUEST );
        print_debug_array( "Post",      $_POST );
        print_debug_array( "Get",       $_GET );

        print_r( "-->\n" );
    }
}

function validate_systemOptions() {
	global $mod_strings;
    $errors = array();
    switch( $_SESSION['setup_db_type'] ){
        case "mysql":
        case "mssql":
        case "oci8":
            break;
        default:
            $errors[] = $mod_strings['ERR_DB_INVALID'];
            break;
    }
    return $errors;
}


function validate_localization() {
	global $mod_strings;
    $errors = array();

	return $errors;		
}


function validate_dbConfig() {
	global $mod_strings;
    $errors = array();

    if( $_SESSION['setup_db_type'] != 'oci8' ){
        if( $_SESSION['setup_db_host_name'] == '' ){
            $errors[] = $mod_strings['ERR_DB_HOSTNAME'];
        }
    }

    if( $_SESSION['setup_db_database_name'] == '' ){
        $errors[] = $mod_strings['ERR_DB_NAME'];
    } elseif( $_SESSION['setup_db_type'] == 'mysql' ){
        if( preg_match( "/[\\\\\/\.]/", $_SESSION['setup_db_database_name'] ) ){
            $errors[] = $mod_strings['ERR_DB_NAME2'];
        }
    }
    
    if( $_SESSION['setup_db_type'] == 'mssql' ) {
        if( preg_match( "/[\\\\\/\.]/", $_SESSION['setup_db_database_name'] ) ) {
           $errors[] = $mod_strings['ERR_DB_NAME2'];
        }
    }
    

    if( $_SESSION['setup_db_sugarsales_user'] == '' ){
        $errors[] = $mod_strings['ERR_DB_USER'];
    }

    if( $_SESSION['setup_db_create_sugarsales_user'] &&
            ($_SESSION['setup_db_sugarsales_password'] != $_SESSION['setup_db_sugarsales_password_retype']) ){
        $errors[] = $mod_strings['ERR_DB_PASSWORD'];
    }

    // bail if the basic info isn't valid
    if( count($errors) > 0 ){
        return( $errors );
    }

    // test the account that will talk to the db if we're not creating it
    if( $_SESSION['setup_db_sugarsales_user'] != '' && !$_SESSION['setup_db_create_sugarsales_user'] ){
        if( $_SESSION['setup_db_type'] == 'mysql' ){
            $link = @mysql_connect( $_SESSION['setup_db_host_name'],
                                    $_SESSION['setup_db_sugarsales_user'],
                                    $_SESSION['setup_db_sugarsales_password'] );
            if( !$link ){
                $errno = mysql_errno();
                $error = mysql_error();
                $errors[] = $mod_strings['ERR_DB_LOGIN_FAILURE_MYSQL']."$errno: $error).";
            }
            else{
                mysql_close( $link );
            }
        } elseif( $_SESSION['setup_db_type'] == 'mssql' ) {
            $link = @mssql_connect( $_SESSION['setup_db_host_name'],
                                    $_SESSION['setup_db_sugarsales_user'],
                                    $_SESSION['setup_db_sugarsales_password'] );
            if( !$link ) {                
                $errors[] = $mod_strings['ERR_DB_LOGIN_FAILURE_MSSQL'];
            } else {
                mssql_close( $link );
            }
        } elseif( $_SESSION['setup_db_type'] == 'oci8' ){
        }
    }

    // privileged account tests
    if( $_SESSION['setup_db_admin_user_name'] == '' ){
        $errors[] = $mod_strings['ERR_DB_PRIV_USER'];
    }
    else {
        if( $_SESSION['setup_db_type'] == 'mysql' ){
            $link = @mysql_connect( $_SESSION['setup_db_host_name'],
                                    $_SESSION['setup_db_admin_user_name'],
                                    $_SESSION['setup_db_admin_password'] );
            if( $link ){
                // database admin credentials are valid--can continue check on stuff

                $db_selected = @mysql_select_db($_SESSION['setup_db_database_name'], $link);
                if( $db_selected && $_SESSION['setup_db_create_database'] ){
                    $errors[] = $mod_strings['ERR_DB_EXISTS'];
                }
                else if( !$db_selected && !$_SESSION['setup_db_create_database'] ){
                    $errors[] = $mod_strings['ERR_DB_EXISTS_NOT'];
                }

                // test for upgrade and inform user about the upgrade wizard
                 if( $db_selected ){
                    $config_query   = "SHOW TABLES LIKE 'config'";
                    $config_result  = mysql_query( $config_query, $link );
                    $config_table_exists    = (mysql_num_rows( $config_result ) == 1);
                    mysql_free_result( $config_result );
					include('sugar_version.php');
                    if( !$_SESSION['setup_db_drop_tables'] && $config_table_exists ){
                        $query = "SELECT COUNT(*) FROM config WHERE category='info' AND name='sugar_version' AND VALUE LIKE '$sugar_db_version'";
                        $result = mysql_query( $query, $link );
                        $row = mysql_fetch_row( $result );
                        if($row[0] != 1) {
                            $errors[] = $mod_strings['ERR_DB_EXISTS_WITH_CONFIG'];
                        }
                        mysql_free_result($result);
                    }
                }


                // check for existing SugarCRM database user
                if($_SESSION['setup_db_create_sugarsales_user'] && $_SESSION['setup_db_sugarsales_user'] != ''){
                    $db_selected = mysql_select_db('mysql', $link);
                    $user = $_SESSION['setup_db_sugarsales_user'];
                    $query = "select count(*) from user where User='$user'";
                    $result = mysql_query($query, $link);
                    $row = mysql_fetch_row($result);

                    if($row[0] == 1){
                        $errors[] = $mod_strings['ERR_DB_USER_EXISTS'];
                    }
                    mysql_free_result($result);
                }

                // check mysql minimum version requirement
                $db_version = getMysqlVersion($link);
                if($db_version < 4.1) {
                    $errors[] = $mod_strings['ERR_DB_MYSQL_VERSION1'].$db_version.$mod_strings['ERR_DB_MYSQL_VERSION2'];
                }

                mysql_close($link);
            }
            else { // dblink was bad
                $errno = mysql_errno();
                $error = mysql_error();
                $errors[] = $mod_strings['ERR_DB_ADMIN'].$errno. ": {$error}).";
            }
        }
        else if( $_SESSION['setup_db_type'] == 'oci8' ){
        }
    } // end of privileged user tests

    return( $errors );
}

function validate_siteConfig(){
	global $mod_strings;
   $errors = array();

   if($_SESSION['setup_site_url'] == ''){
      $errors[] = $mod_strings['ERR_URL_BLANK'];
   }
   
   if($_SESSION['parent_setup_site_url'] == ''){
      $errors[] = $mod_strings['ERR_PARENT_URL_BLANK'];
   }

   if($_SESSION['setup_site_portal_username'] == ''){
      $errors[] = $mod_strings['ERR_ADMIN_NAME_BLANK'];
   }
   
   if($_SESSION['setup_site_portal_password'] == ''){
      $errors[] = $mod_strings['ERR_ADMIN_PASS_BLANK'];
   }

   if(!empty($_SESSION['setup_site_custom_session_path']) && $_SESSION['setup_site_session_path'] == ''){
      $errors[] = $mod_strings['ERR_SESSION_PATH'];
   }

   if(!empty($_SESSION['setup_site_custom_session_path']) && $_SESSION['setup_site_session_path'] != ''){
      if(is_dir($_SESSION['setup_site_session_path'])){
         if(!is_writable($_SESSION['setup_site_session_path'])){
            $errors[] = $mod_strings['ERR_SESSION_DIRECTORY'];
         }
      }
      else {
         $errors[] = $mod_strings['ERR_SESSION_DIRECTORY_NOT_EXISTS'];
      }
   }

   if(!empty($_SESSION['setup_site_custom_log_dir']) && $_SESSION['setup_site_log_dir'] == ''){
      $errors[] = $mod_strings['ERR_LOG_DIRECTORY_NOT_EXISTS'];
   }

   if(!empty($_SESSION['setup_site_custom_log_dir']) && $_SESSION['setup_site_log_dir'] != ''){
      if(is_dir($_SESSION['setup_site_log_dir'])){
         if(!is_writable($_SESSION['setup_site_log_dir'])) {
            $errors[] = $mod_strings['ERR_LOG_DIRECTORY_NOT_WRITABLE'];
         }
      }
      else {
         $errors[] = $mod_strings['ERR_LOG_DIRECTORY_NOT_EXISTS'];
      }
   }

   if(!empty($_SESSION['setup_site_specify_guid']) && $_SESSION['setup_site_guid'] == ''){
      $errors[] = $mod_strings['ERR_SITE_GUID'];
   }

   // do sanity check to see if portal is able to connect to the parent
   if(!empty($_SESSION['setup_site_url']) &&
      !empty($_SESSION['parent_setup_site_url']) &&
      !empty($_SESSION['setup_site_portal_username']) &&
      !empty($_SESSION['setup_site_portal_password'])) {
        require_once("include/nusoap/nusoap.php");
        $soapClient = new nusoapclient($_SESSION['parent_setup_site_url'] . '/soap.php?wsdl', true, false, false, false, false, 50, 50);

        $err = $soapClient->getError();
        if($err) { // check url is correct
            if(strpos($err, 'XML error parsing WSDL')) {
              $errors[] = str_replace('{0}', "{$_SESSION['parent_setup_site_url']}/soap.php?wsdl", $mod_strings['ERR_PARSING_WSDL']); // Could not obtain WSDL
            } else {
              $errors[] = $mod_strings['ERR_PARENT_CONNECT']; // parent_url is bad
            }
        }
        else { // check portal option is enabled
            $soapClientProxy = $soapClient->getProxy();
            
            if(empty($soapClientProxy->operations['portal_login_contact'])) { // portal not turned on on parent
                $errors[] = $mod_strings['ERR_PARENT_SOAP'];
            }
            else { // check username and password
                $result = $soapClientProxy->portal_login(array('user_name' => $_SESSION['setup_site_portal_username'], 'password' => md5($_SESSION['setup_site_portal_password']), 'version' => '.01'), 'portal', 'SoapTest');
                if(empty($result)) {
                    $errors[] = $mod_strings['ERR_PARENT_SITEURL'];
                }
                elseif(isset($result['error']) && $result['error']['number'] != 0) {
                    $errors[] = $mod_strings['ERR_SOAP_USER_PASS'];
                }
                
            }
        }
      }
      
   return $errors;
}


function pullSilentInstallVarsIntoSession() {
	global $mod_strings;
	global $sugar_config;

    require_once('config.php');
    if( file_exists('config_si.php') ){
        require_once('config_si.php');
    }
    else if( empty($sugar_config_si) ){
        die( $mod_strings['ERR_SI_NO_CONFIG'] );
    }

    $config_subset = array (
        'setup_site_url'                => $sugar_config['site_url'],
        'setup_db_host_name'            => $sugar_config['dbconfig']['db_host_name'],
        'setup_db_sugarsales_user'      => $sugar_config['dbconfig']['db_user_name'],
        'setup_db_sugarsales_password'  => $sugar_config['dbconfig']['db_password'],
        'setup_db_database_name'        => $sugar_config['dbconfig']['db_name'],
        'setup_db_type'                 => $sugar_config['dbconfig']['db_type'],
    );

    // third array of values derived from above values
    $derived = array (
        'setup_site_admin_password_retype'      => $sugar_config_si['setup_site_admin_password'],
        'setup_db_sugarsales_password_retype'   => $config_subset['setup_db_sugarsales_password'],
        //BEGIN SUGARCRM flav=pro ONLY 
        'oc_run' => 'convert',
         //END SUGARCRM flav=pro ONLY 
    );
    //BEGIN SUGARCRM flav=pro ONLY 
    if(isset($sugar_config_si['oc_server_url']))
        $derived['oc_server_url'] = $sugar_config_si['oc_server_url'];
    if(isset($sugar_config_si['oc_username']))
        $derived['oc_username'] = $sugar_config_si['oc_username'];
    if(isset($sugar_config_si['oc_password']))
        $derived['oc_password'] = $sugar_config_si['oc_password'];
    //END SUGARCRM flav=pro ONLY 

    $all_config_vars = array_merge( $config_subset, $sugar_config_si, $derived );

    foreach( $all_config_vars as $key => $value ){
        $_SESSION[$key] = $value;
    }
}

function validate_offlineClientConfig() {
	global $mod_strings;
    $errors = array();
    if(empty($_SESSION['oc_server_url']) || $_SESSION['oc_server_url'] == 'http://'){
    	 $errors[] = $mod_strings['ERR_OC_SERVER_URL'];
    }
    if(empty($_SESSION['oc_username'])){
    	 $errors[] = $mod_strings['ERR_OC_USERNAME'];
    }
    if(empty($_SESSION['oc_password'])){
    	 $errors[] = $mod_strings['ERR_OC_PASSWORD'];
    }
    return $errors;
}


/**
 * handles language pack uploads - code based off of upload_file->final_move()
 * puts it into the cache/upload dir to be handed off to langPackUnpack();
 * 
 * @param object file UploadFile object
 * @return bool true if successful
 */
function langPackFinalMove($file) {
    global $sugar_config;
    //."upgrades/langpack/"
    $destination = $sugar_config['upload_dir'].$file->stored_file_name;
    if(!move_uploaded_file($_FILES[$file->field_name]['tmp_name'], $destination)) {
        die ("ERROR: can't move_uploaded_file to $destination. You should try making the directory writable by the webserver");
    }
    return true;
}


/**
 * creates the remove/delete form for langpack page
 * @param string type commit/remove
 * @param string manifest path to manifest file
 * @param string zipFile path to uploaded zip file
 * @param int nextstep current step
 * @return string ret <form> for this package
 */
function getPackButton($type, $manifest, $zipFile, $next_step, $uninstallable='Yes', $showButtons=true) {
    global $mod_strings;
    
    $button = $mod_strings['LBL_LANG_BUTTON_COMMIT'];
    if($type == 'remove') {
        $button = $mod_strings['LBL_LANG_BUTTON_REMOVE'];
    } elseif($type == 'uninstall') {
    	$button = $mod_strings['LBL_LANG_BUTTON_UNINSTALL'];
    }
    
    $disabled = ($uninstallable == 'Yes') ? false : true;
    
    $ret = "<form name='delete{$zipFile}' action='install.php' method='POST'>
                <input type='hidden' name='current_step' value='{$next_step}'>
                <input type='hidden' name='goto' value='{$mod_strings['LBL_CHECKSYS_RECHECK']}'>
                <input type='hidden' name='languagePackAction' value='{$type}'>
                <input type='hidden' name='manifest' value='".urlencode($manifest)."'>
                <input type='hidden' name='zipFile' value='".urlencode($zipFile)."'>";
    if(!$disabled && $showButtons) {
		$ret .=	"<input type='submit' value='{$button}'>";
    }
    $ret .= "</form>";
    return $ret;
}

/**
 * finds all installed languages and returns an array with the names
 * @return array langs array of installed languages
 */
function getInstalledLanguages() {
	$langDir = 'include/language/';
	$dh = opendir($langDir);
	
	$langs = array();
	while($file = readdir($dh)) {
		if(substr($file, -3) == 'php') {
			
		}
	}
}



/**
 * searches upgrade dir for lang pack files.
 * 
 * @return string HTML of available lang packs
 */
function getLangPacks() {
    global $mod_strings;
    global $next_step;
    global $base_upgrade_dir;
    
	$ret = "<tr><td colspan=7 align=left>{$mod_strings['LBL_LANG_PACK_READY']}</td></tr>";
    $ret .= "<tr>
	            <td nowrap align=left><b>{$mod_strings['LBL_ML_NAME']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_VERSION']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_PUBLISHED']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_UNINSTALLABLE']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_DESCRIPTION']}</b></td>
            </tr>\n";
    $files = array();
    
    // duh, new installs won't have the upgrade folders
    if(!is_dir(getcwd()."/cache/upload/upgrades")) {
		$subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');
    	foreach( $subdirs as $subdir ){
		    mkdir_recursive( "$base_upgrade_dir/$subdir" );
		}
    }
    
    $files = findAllFiles(getcwd()."/cache/upload/upgrades", $files);
    
    foreach($files as $file) {
        if(!preg_match("#.*\.zip\$#", $file)) {
            continue;
        }
        
        // skip installed lang packs
        if(isset($_SESSION['INSTALLED_LANG_PACKS']) && in_array($file, $_SESSION['INSTALLED_LANG_PACKS'])) {
        	continue;
        }
        
        // handle manifest.php
        $target_manifest = remove_file_extension( $file ) . '-manifest.php';
        include($target_manifest);

        $name = empty($manifest['name']) ? $file : $manifest['name'];
        $version = empty($manifest['version']) ? '' : $manifest['version'];
        $published_date = empty($manifest['published_date']) ? '' : $manifest['published_date'];
        $icon = '';
        $description = empty($manifest['description']) ? 'None' : $manifest['description'];
        $uninstallable = empty($manifest['is_uninstallable']) ? 'No' : 'Yes';
        $manifest_type = $manifest['type'];
        $commitPackage = getPackButton('commit', $target_manifest, $file, $next_step);
        $deletePackage = getPackButton('remove', $target_manifest, $file, $next_step);

        $ret .= "<tr>";
        $ret .= "<td nowrap>".$name."</td>";
        $ret .= "<td nowrap>".$version."</td>";
        $ret .= "<td nowrap>".$published_date."</td>";
        $ret .= "<td nowrap>".$uninstallable."</td>";
        $ret .= "<td>".$description."</td>";    
        $ret .= "<td nowrap>{$commitPackage}</td>";
        $ret .= "<td nowrap>{$deletePackage}</td>";
        $ret .= "</td></tr>";
    }
    
    if(count($files) > 0 ) {
        $ret .= "</tr><td colspan=7>";
        $ret .= "<form name='commit' action='install.php' method='POST'>
                    <input type='hidden' name='current_step' value='{$next_step}'>
                    <input type='hidden' name='goto' value='Re-check'>
                    <input type='hidden' name='languagePackAction' value='commit'>
                 </form>
                ";
        $ret .= "</td></tr>";
    } else {
        $ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
    }
    return $ret;
}

function extractFile( $zip_file, $file_in_zip, $base_tmp_upgrade_dir){
    $my_zip_dir = mk_temp_dir( $base_tmp_upgrade_dir );
    unzip_file( $zip_file, $file_in_zip, $my_zip_dir );
    return( "$my_zip_dir/$file_in_zip" );
}

function extractManifest( $zip_file,$base_tmp_upgrade_dir ) {
    return( extractFile( $zip_file, "manifest.php",$base_tmp_upgrade_dir ) );
}

function unlinkTempFiles($manifest, $zipFile) {
    global $sugar_config;
    
    @unlink($_FILES['language_pack']['tmp_name']);
    if(!empty($manifest))
    	@unlink($manifest);
    if(!empty($zipFile)) {
	    //@unlink($zipFile);
	    $tmpZipFile = substr($zipFile, strpos($zipFile, 'langpack/') + 9, strlen($zipFile));
	    @unlink(getcwd()."/".$sugar_config['upload_dir'].$tmpZipFile);
    }
    
    rmdir_recursive(getcwd()."/".$sugar_config['upload_dir']."upgrades/temp");
	mkdir(getcwd()."/".$sugar_config['upload_dir']."upgrades/temp");
}


function langPackUnpack() {
    global $sugar_config;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;
    
    $manifest = array();
    $tempFile = getcwd().'/'.$sugar_config['upload_dir'].$_FILES['language_pack']['name'];
    $manifest_file = extractManifest($tempFile, $base_tmp_upgrade_dir);
    
    if(is_file($manifest_file)) {
    	
        copy($manifest_file, getcwd().'/'.$sugar_config['upload_dir'].'upgrades/langpack/'.remove_file_extension($_FILES['language_pack']['name'])."-manifest.php");
        
        require_once( $manifest_file );
        validate_manifest( $manifest );
        $upgrade_zip_type = $manifest['type'];

        // exclude the bad permutations
        if($upgrade_zip_type != "langpack") {
            unlinkTempFiles($manifest_file, $tempFile);
            die( "You can only upload module packs, theme packs, and language packs on this page." );
        }

        $base_filename = urldecode( $_REQUEST['language_pack_escaped'] );
        $base_filename = preg_replace( "#\\\\#", "/", $base_filename );
        $base_filename = basename( $base_filename );
        
        mkdir_recursive( "$base_upgrade_dir/$upgrade_zip_type" );
        $target_path = getcwd()."/$base_upgrade_dir/$upgrade_zip_type/$base_filename";
        $target_manifest = remove_file_extension( $target_path ) . "-manifest.php";
    
        if( isset($manifest['icon']) && $manifest['icon'] != "" ) {
            $icon_location = extractFile( $tempFile, $manifest['icon'], $base_tmp_upgrade_dir );
            $path_parts = pathinfo( $icon_location );
            copy( $icon_location, remove_file_extension( $target_path ) . "-icon." . $path_parts['extension'] );
        }

		// move file from cache/upload to cache/upload/langpack
        if( copy( $tempFile , $target_path ) ){
            copy( $manifest_file, $target_manifest );
            unlink($tempFile); // remove tempFile
            return "The file $base_filename has been uploaded.<br>\n";
        } else {
        	unlinkTempFiles($manifest_file, $tempFile);
            return "There was an error uploading the file, please try again!<br>\n";
        }
    } else {
        die("The zip file is missing a manifest.php file.  Cannot proceed.");
    }
    unlinkTempFiles($manifest_file, '');
}

function validate_manifest( $manifest ){
    // takes a manifest.php manifest array and validates contents
    global $subdirs;
    global $sugar_version;
    global $sugar_flavor;
    global $mod_strings;

    if( !isset($manifest['type']) ){
        die($mod_strings['ERROR_MANIFEST_TYPE']);
    }
    $type = $manifest['type'];
    if( getInstallType( "/$type/" ) == "" ){
        die($mod_strings['ERROR_PACKAGE_TYPE']. ": '" . $type . "'." );
    }
    
    return true; // making this a bit more relaxed since we updated the language extraction and merge capabilities
    
	/*
    if( isset($manifest['acceptable_sugar_versions']) ){
        $version_ok = false;
        $matches_empty = true;
        if( isset($manifest['acceptable_sugar_versions']['exact_matches']) ){
            $matches_empty = false;
            foreach( $manifest['acceptable_sugar_versions']['exact_matches'] as $match ){
                if( $match == $sugar_version ){
                    $version_ok = true;
                }
            }
        }
        if( !$version_ok && isset($manifest['acceptable_sugar_versions']['regex_matches']) ){
            $matches_empty = false;
            foreach( $manifest['acceptable_sugar_versions']['regex_matches'] as $match ){
                if( preg_match( "/$match/", $sugar_version ) ){
                    $version_ok = true;
                }
            }
        }

        if( !$matches_empty && !$version_ok ){
            die( $mod_strings['ERROR_VERSION_INCOMPATIBLE'] . $sugar_version );
        }
    }

    if( isset($manifest['acceptable_sugar_flavors']) && sizeof($manifest['acceptable_sugar_flavors']) > 0 ){
        $flavor_ok = false;
        foreach( $manifest['acceptable_sugar_flavors'] as $match ){
            if( $match == $sugar_flavor ){
                $flavor_ok = true;
            }
        }
        if( !$flavor_ok ){
            //die( $mod_strings['ERROR_FLAVOR_INCOMPATIBLE'] . $sugar_version );
        }
    }*/
}

function getInstallType( $type_string ){
    // detect file type
    $subdirs = array('full', 'langpack', 'module', 'patch', 'theme', 'temp');
    foreach( $subdirs as $subdir ){
        if( preg_match( "#/$subdir/#", $type_string ) ){
            return( $subdir );
        }
    }
    // return empty if no match
    return( "" );
}

function removeLanguagePack() {
    global $mod_strings;
    
    $errors = array();
    $manifest = urldecode($_REQUEST['manifest']);
    $zipFile = urldecode($_REQUEST['zipFile']);
    
    if(isset($manifest) && !empty($manifest)) {
        if(is_file($manifest)) {
            if(!unlink($manifest)) {
                $errors[] = $mod_strings['ERR_LANG_CANNOT_DELETE_FILE'].$manifest;
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$manifest;
        }
    }
    if(isset($zipFile) && !empty($zipFile)) {
        if(is_file($zipFile)) {
            if(!unlink($zipFile)) {
                $errors[] = $mod_strings['ERR_LANG_CANNOT_DELETE_FILE'].$zipFile;
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$zipFile;
        }
    }
    if(count($errors > 0)) {
        echo "<p class='error'>";
        foreach($errors as $error) {
            echo "{$error}<br>";
        }
        echo "</p>";
    }
    
    unlinkTempFiles($manifest, $zipFile);
}

/**
 * copies the temporary unzip'd files to their final destination
 * removes unzip'd files from system if $uninstall=true
 * @param bool uninstall true if uninstalling a language pack
 * @return null stop execution if trying to double-install a pack
 */
function commitLanguagePack($uninstall=false) {
    global $mod_strings;
    global $base_upgrade_dir;
    global $base_tmp_upgrade_dir;
    
    $errors         = array();
    $manifest       = urldecode($_REQUEST['manifest']);
    $zipFile        = urldecode($_REQUEST['zipFile']);
    $version        = "";
    $show_files     = true;
    $unzip_dir      = mk_temp_dir( $base_tmp_upgrade_dir );
    $zip_from_dir   = ".";
    $zip_to_dir     = ".";
    $zip_force_copy = array();
    
    if($uninstall == false && isset($_SESSION['INSTALLED_LANG_PACKS']) && in_array($zipFile, $_SESSION['INSTALLED_LANG_PACKS'])) {
    	return;	
    }
    
    // unzip lang pack to temp dir    
    if(isset($zipFile) && !empty($zipFile)) {
        if(is_file($zipFile)) {
            unzip( $zipFile, $unzip_dir );
        } else {
            echo $mod_strings['ERR_LANG_MISSING_FILE'].$zipFile;
            die(); // no point going any further
        }
    }
    
    // filter for special to/from dir conditions (langpacks generally don't have them)
    if(isset($manifest) && !empty($manifest)) {
        if(is_file($manifest)) {
            include($manifest);
            if( isset( $manifest['copy_files']['from_dir'] ) && $manifest['copy_files']['from_dir'] != "" ){
                $zip_from_dir   = $manifest['copy_files']['from_dir'];
            }
            if( isset( $manifest['copy_files']['to_dir'] ) && $manifest['copy_files']['to_dir'] != "" ){
                $zip_to_dir     = $manifest['copy_files']['to_dir'];
            }
            if( isset( $manifest['copy_files']['force_copy'] ) && $manifest['copy_files']['force_copy'] != "" ){
                $zip_force_copy     = $manifest['copy_files']['force_copy'];
            }
            if( isset( $manifest['version'] ) ){
                $version    = $manifest['version'];
            }
        } else {
            $errors[] = $mod_strings['ERR_LANG_MISSING_FILE'].$manifest;
        }
    }
    

    // find name of language pack: find single file in include/language/xx_xx.lang.php
    $d = dir( "$unzip_dir/$zip_from_dir/include/language" );
    while( $f = $d->read() ){
        if( $f == "." || $f == ".." ){
            continue;
        }
        else if( preg_match("/(.*)\.lang\.php\$/", $f, $match) ){
            $new_lang_name = $match[1];
        }
    }
    if( $new_lang_name == "" ){
        die( $mod_strings['ERR_LANG_NO_LANG_FILE'].$zipFile );
    }
    $new_lang_desc = getLanguagePackName( "$unzip_dir/$zip_from_dir/include/language/$new_lang_name.lang.php" );
    if( $new_lang_desc == "" ){
        die( "No language pack description found at include/language/$new_lang_name.lang.php inside $install_file." );
    }
	// add language to available languages
    $sugar_config['languages'][] = array( $new_lang_name => $new_lang_desc );
    
    // get an array of all files to be moved
    $filesFrom = array();
    $filesFrom = findAllFiles($unzip_dir, $filesFrom);
    
    if($uninstall) {
		// unlink all pack files
		foreach($filesFrom as $fileFrom) {
			//echo "deleting: ".getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom))."<br>";
			@unlink(getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom)));
		}
		// remove session entry
		if(isset($_SESSION['INSTALLED_LANG_PACKS']) && is_array($_SESSION['INSTALLED_LANG_PACKS'])) {
			foreach($_SESSION['INSTALLED_LANG_PACKS'] as $k => $langPack) {
				if($langPack == $zipFile) {
					unset($_SESSION['INSTALLED_LANG_PACKS'][$k]);
				}
			}
		}
    } else {
	    // copy filesFrom to filesTo
	    foreach($filesFrom as $fileFrom) {
	    	@copy($fileFrom, getcwd().substr($fileFrom, strlen($unzip_dir), strlen($fileFrom))); 
	    }
	    
	    $_SESSION['INSTALLED_LANG_PACKS'][] = $zipFile;
    }
}

function uninstallLangPack() {
	global $sugar_config;
	
	// remove language from config
	$new_langs = array();
	$old_langs = $sugar_config['languages'];
	foreach( $old_langs as $key => $value ){
	    if( $key != $_REQUEST['new_lang_name'] ){
	        $new_langs += array( $key => $value );
	    }
	}
	$sugar_config['languages'] = $new_langs;
}


function getLanguagePackName( $the_file ){
    require_once( "$the_file" );
    if( isset( $app_list_strings["language_pack_name"] ) ){
        return( $app_list_strings["language_pack_name"] );
    }
    return( "" );
}


function getInstalledLangPacks($showButtons=true) {
    global $mod_strings;
    global $next_step;
    
	$ret  = "<tr><td colspan=7 align=left>{$mod_strings['LBL_LANG_PACK_INSTALLED']}</td></tr>";
    $ret .= "<tr>
	            <td nowrap align=left><b>{$mod_strings['LBL_ML_NAME']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_VERSION']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_PUBLISHED']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_UNINSTALLABLE']}</b></td>
	            <td nowrap><b>{$mod_strings['LBL_ML_DESCRIPTION']}</b></td>
            </tr>\n";
    $files = array();
    if(file_exists(getcwd()."/cache/upload/upgrades")) {
    	$files = findAllFiles(getcwd()."/cache/upload/upgrades", $files);
    }
    
    if(isset($_SESSION['INSTALLED_LANG_PACKS']) && !empty($_SESSION['INSTALLED_LANG_PACKS'])){
    	if(count($_SESSION['INSTALLED_LANG_PACKS'] > 0)) {
		    foreach($_SESSION['INSTALLED_LANG_PACKS'] as $file) {
		        // handle manifest.php
		        $target_manifest = remove_file_extension( $file ) . '-manifest.php';
		        include($target_manifest);
		        
		        $name = empty($manifest['name']) ? $file : $manifest['name'];
		        $version = empty($manifest['version']) ? '' : $manifest['version'];
		        $published_date = empty($manifest['published_date']) ? '' : $manifest['published_date'];
		        $icon = '';
		        $description = empty($manifest['description']) ? 'None' : $manifest['description'];
		        $uninstallable = empty($manifest['is_uninstallable']) ? 'No' : 'Yes';
		        $manifest_type = $manifest['type'];
		        $deletePackage = getPackButton('uninstall', $target_manifest, $file, $next_step, $uninstallable, $showButtons);
		
		        $ret .= "<tr>";
		        $ret .= "<td nowrap>".$name."</td>";
		        $ret .= "<td nowrap>".$version."</td>";
		        $ret .= "<td nowrap>".$published_date."</td>";
		        $ret .= "<td nowrap>".$uninstallable."</td>";
		        $ret .= "<td>".$description."</td>";    
		        $ret .= "<td nowrap></td>";
		        $ret .= "<td nowrap>{$deletePackage}</td>";
		        $ret .= "</td></tr>";
		    }
    	} else {
    		$ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
    	}
    } else {
		$ret .= "</tr><td colspan=7><i>{$mod_strings['LBL_LANG_NO_PACKS']}</i></td></tr>";
    }    
    return $ret;
}


function uninstallLanguagePack() {
	commitLanguagePack(true);
}


function getSugarConfigLanguageArray($langZip) {
	global $sugar_config;
	
	include(remove_file_extension($langZip)."-manifest.php");
	$ret = '';
	if(isset($installdefs['id']) && isset($manifest['name'])) {
		$ret = $installdefs['id']."::".$manifest['name']."::".$manifest['version'];
	}
	
	return $ret;
}

?>
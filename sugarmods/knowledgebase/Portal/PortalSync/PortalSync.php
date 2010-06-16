<?php
/*
 * Created on Jan 24, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once('include/Sugar_Smarty.php');
 require_once('include/utils.php');
 require_once("include/nusoap/nusoap.php");
 require_once ('include/dir_inc.php');
require_once ('include/utils/file_utils.php');
require_once ('include/utils/zip_utils.php');

 class PortalSync{
	
 	function PortalSync(){
 		
 	}
 	
 	function display(){
 		global $mod_strings;
 		require_once ('PortalSync/language/en_us.lang.php');
 		$ss = new Sugar_Smarty();
 		$ss->assign('MOD', $mod_strings);
 		$ss->assign('scripts', $this->displayScripts());
 		$str = $ss->fetch('PortalSync/tpls/PortalSync.tpl'); 
 		return $str;
 	}
 	
 	function displayScripts(){
 		require_once('config.php');
 		require_once('sugar_version.php');
 		global $mod_strings;
 		$ss = new Sugar_Smarty();
 		$ss->assign('sugar_version', $sugar_version);
        $ss->assign('js_custom_version', $sugar_config['js_custom_version']);
 		$ss->assign('MOD', $mod_strings);
 		$str = $ss->fetch('PortalSync/tpls/PortalSyncScripts.tpl'); 
 		return $str;
 	}
 	
 	function login(){
 		require_once('include/json_config.php');
 		require_once('config.php');
 		
        $json_config = new json_config();
        $json = getJSONobj();
        $session = '';
        $user_name = '';
        $password = '';
        if(isset($_REQUEST['user_name'])) {
                $user_name = nl2br($_REQUEST['user_name']);           
        } 
        if(isset($_REQUEST['password'])) {
                $password = nl2br($_REQUEST['password']);           
        }
        
        $soapclient = new nusoapclient($sugar_config['parent_site_url'] . '/soap.php?wsdl', true);
        $result = $soapclient->call('login',array('user_auth'=>array('user_name'=>$user_name,'password'=>md5($password), 'version'=>'1.0'), 'application_name'=>'PortalSync'));
        $session = $result['id'];
        if(strcmp($session, '-1') != 0){
        	if(!$soapclient->call('is_user_admin',array('session'=>$session))){
        		$soapclient->call('logout', array('session'=>$session));
        		$session = -1;
        	}
        }
        echo 'result = ' . $json->encode(array('result' => $session));
 	}
 	
	function beginSync(){
		require_once('include/json_config.php');
		global $sugar_config;
		require_once('config.php');
		
        $json_config = new json_config();
        $json = getJSONobj();
        $session = '';
        if(isset($_REQUEST['session'])) {
                $session = nl2br($_REQUEST['session']);           
        } 
        
        if(!empty($session)){
        	$result = $this->performSync($session);
        }else{
        	$result = '-1';
        }
        echo 'result = ' . $json->encode(array('result' => $result));
	}
	
	function performSync($session, $force_md5_sync = false){
		global $sugar_config;
		require_once ('include/TimeDate.php');
		$timedate = new TimeDate();
		ini_set( "memory_limit", "-1" );
    	set_time_limit(3600);
		ini_set('default_socket_timeout', 3600);
		$return_str  = "";
    	 $soapclient = new nusoapclient($sugar_config['parent_site_url'] . '/soap.php?wsdl', true);
    	//1) rather than using md5, we will use the date_modified
    	if (file_exists('PortalSync/file_config.php') && $force_md5_sync != true) {
			require_once ('PortalSync/file_config.php');
        	//global $file_sync_info;
			if(!isset($file_sync_info['last_local_sync']) && !isset($file_sync_info['last_server_sync'])){
				$last_local_sync = $timedate->get_gmt_db_datetime();	
    			$last_server_sync = $timedate->get_gmt_db_datetime();	
    			$is_first_sync = true;
			}else{	
				$last_local_sync = $file_sync_info['last_local_sync'];
				$last_server_sync = $file_sync_info['last_server_sync'];
				$is_first_sync = false;
			}
    	}else{
    		$last_local_sync = $timedate->get_gmt_db_datetime();	
    		$last_server_sync = $timedate->get_gmt_db_datetime();	
    		$is_first_sync = true;
    	}

    	$temp_file  = tempnam(getcwd()."/".$sugar_config['tmp_dir'], "sug" );
    	$file_list = array();
    	if(!$is_first_sync){
    		$all_src_files  = findAllTouchedFiles( "modules", array(), $last_local_sync);

    		foreach( $all_src_files as $src_file ){
        		$file_list[$src_file] = $src_file;
    		}
    	}else{
    		$all_src_files  = findAllFiles( "modules", array());
    	 	require("install/data/disc_client.php");
    	 	foreach( $all_src_files as $src_file ){
    			foreach($disc_client_ignore as $ignore_pattern ){
            		if(!preg_match( "#" . $ignore_pattern . "#", $src_file ) ){
                		$md5 = md5_file( $src_file );
        				$file_list[$src_file] = $md5;
            		}//fi
        		}//rof
    		}//rof	
   	 	}//else
   	 	
   	 	//2) save the list of md5 files to file system
    	if( !write_array_to_file( "client_file_list", $file_list, $temp_file ) ){
        	return "Could not save file.";
    	}

		$md5 = md5_file($temp_file);
		// read file
    	$fh = fopen($temp_file, "rb" );
    	$contents = fread( $fh, filesize($temp_file) );
    	fclose( $fh );
	
    	// encode data
    	$data = base64_encode($contents);
   		$md5file  = array('filename'=>$temp_file, 'md5'=>$md5, 'data'=>$data, 'error' => null);
   		
		$result = $soapclient->call('get_encoded_portal_zip_file', array( 'session'=>$session, 'md5file'=>$md5file, 'last_sync' => $last_server_sync, 'is_md5_sync' => $is_first_sync));

    	//3) at this point we could have the zip file
    	$zip_file = tempnam(getcwd()."/".$sugar_config['tmp_dir'], "zip" ).'.zip';
    	if(isset($result['result']) && !empty($result['result'])){
    		
    		$fh = fopen($zip_file, 'w');
    		fwrite($fh, base64_decode($result['result']));
			fclose($fh);
	
    		$archive = new PclZip($zip_file);
    		if( $archive->extract( PCLZIP_OPT_PATH, ".", 
    					   PCLZIP_OPT_REPLACE_NEWER) == 0 ){
        		die( "Error: " . $archive->errorInfo(true) );
    		}
    	}
    
	    if(file_exists($zip_file)){
	       unlink($zip_file);
	    }
		$file_sync_info['last_local_sync'] = $timedate->get_gmt_db_datetime();
		$server_time = $soapclient->call('get_gmt_time', array ());
		$file_sync_info['last_server_sync'] = $server_time;
		$file_sync_info['is_first_sync'] = $is_first_sync;
		write_array_to_file('file_sync_info', $file_sync_info, 'PortalSync/file_config.php');
        
        // clear out cache of templates so they will be rebuilt
        $files = array();
        getFiles($files, 'cache/modules');
        foreach($files as $file) {
            if(is_file($file) && (strpos($file, '.tpl') ||strpos($file, '.html'))) {
              	unlink($file);
            }
        }
        // clear out cache of templates so they will be rebuilt
        $files = array();
        getFiles($files, 'cache/moduleFields');
        foreach($files as $file) {
            if(is_file($file) && strpos($file, '.php')) {
                unlink($file);
            }
        }

		// clear out cache of templates so they will be rebuilt
        $files = array();
        getFiles($files, 'cache/images');
        foreach($files as $file) {
            if(is_file($file) && !strpos($file, '.html')) {
                unlink($file);
            }
        }
        
        $this->merge_files('Ext/Include', 'modules.ext.php', '', true);	

        // run any custom UpgradeConfig files...
        if(file_exists('custom/Extension/application/Ext/Include')) {
           getFiles($files, 'custom/Extension/application/Ext/Include');
           foreach($files as $file) {
           	       if(preg_match("/UpdateConfig[\.]php/si", $file)) {
           	       	  @require_once($file);
           	       }
           } //foreach
        }

		return true;
	}//func
	
 function merge_files($path, $name, $filter = '', $application = false){
		if(!$application){
		$modules = get_module_dir_list();	
		foreach($modules as $module){
				$extension = "<?php \n //WARNING: The contents of this file are auto-generated\n";
				$extpath = "modules/$module/$path";
				$module_install  = 'custom/Extension/'.$extpath;
				$shouldSave = false;
				if(is_dir($module_install)){
					$dir = dir($module_install);
					$shouldSave = true;
					while($entry = $dir->read()){
							if((empty($filter) || substr_count($entry, $filter) > 0) && is_file($module_install.'/'.$entry) && $entry != '.' && $entry != '..'){
								$fp = fopen($module_install . '/' . $entry, 'r');
								$file = fread($fp , filesize($module_install . '/' . $entry));
								fclose($fp);
								$extension .= "\n". str_replace(array('<?php', '?>', '<?PHP', '<?'), array('','', '' ,'') , $file);
							}
					}	
				}
				$extension .= "\n?>";
				
				if($shouldSave){
					if(!file_exists("custom/$extpath")){
					mkdir_recursive("custom/$extpath", true);
				}
					$out = fopen("custom/$extpath/$name", 'w');
					fwrite($out,$extension);
					fclose($out);	
				}else{
					if(file_exists("custom/$extpath/$name")){
						unlink("custom/$extpath/$name");	
					}
				}
			}
				
		}
 		
		//Now the application stuff
		$extension = "<?php \n //WARNING: The contents of this file are auto-generated\n";
		$extpath = "application/$path";
		$module_install  = 'custom/Extension/'.$extpath;
		$shouldSave = false;
					if(is_dir($module_install)){
						$dir = dir($module_install);
						while($entry = $dir->read()){
								$shouldSave = true;
								if((empty($filter) || substr_count($entry, $filter) > 0) && is_file($module_install.'/'.$entry) && $entry != '.' && $entry != '..'){
									$fp = fopen($module_install . '/' . $entry, 'r');
									$file = fread($fp , filesize($module_install . '/' . $entry));
									fclose($fp);
									$extension .= "\n". str_replace(array('<?php', '?>', '<?PHP', '<?'), array('','', '' ,'') , $file);
								}
						}	
					}
					$extension .= "\n?>";
					if($shouldSave){
						if(!file_exists("custom/$extpath")){
							mkdir_recursive("custom/$extpath", true);
						}
						$out = fopen("custom/$extpath/$name", 'w');
						fwrite($out,$extension);
						fclose($out);	
					}else{
					if(file_exists("custom/$extpath/$name")){
						unlink("custom/$extpath/$name");	
					}
				}
 }
 
 

 }


?>

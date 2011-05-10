<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
/*
 *set the config_options below and then run this php file for quick setup
 *if you wish not to take the defaults, then go back after set up and modify dce_config file.
 *
 */

    require_once('client_utils.php');

    $config_options = array();
    $config_options['dce_dbServer'] = 'localhost';//dce db host
    $config_options['dce_dbUser']   = 'sugar';//dce db user
    $config_options['dce_dbPass']   = 'sugar';//dce db pass
    $config_options['dce_dbName']   = 'dce';//dce db
    $config_options['client_dir_path']  = '/var/www/'; //path to web root

    
   

    //make client instance directory
    $client_path = checkSlash($config_options['client_dir_path']).'sugarclient';
    if(file_exists($client_path)){
        //remove the file so we start clean
        shell_exec("rm -rf '".$client_path."'");   
    }
        
    //copy all files from current directory to sugarclient
    $cwd = getcwd();
    $cwd = checkSlash($cwd);
    $cp_script = sprintf("cp -rf '%s' '%s' ",$cwd, $client_path);
    //note we are not yet using php copy() because we need the call to be recursive.
    shell_exec($cp_script);
    shell_exec("chmod 755 '".$client_path. "' -R");

    //make web directory
    $web_path = checkSlash($config_options['client_dir_path']).'web';
    if(file_exists($web_path)){
        //remove the file so we start clean
        shell_exec("rm -rf '".$web_path."'");   
    }
    mkdir($web_path);
    shell_exec("chmod 755 '".$web_path. "' -R");
    
    //make archive directory
    $arch_path = checkSlash($config_options['client_dir_path']).'sugarclient/archives';
    if(file_exists($arch_path)){
        //remove the file so we start clean
        shell_exec("rm -rf '".$arch_path."'");   
    }
    mkdir($arch_path);
    shell_exec("chmod 755 '".$arch_path. "' -R");
    

    //make template directory
    $temp_path = checkSlash($config_options['client_dir_path']).'templates';
    if(!file_exists($temp_path)){
        //Create this directory only if it does not exist.  We do not want to delete
        mkdir($temp_path);   
    }



    //make cron job

    //write or rewrite new cron file
    $cronPath = checkSlash($config_options['client_dir_path']).'sugarclient/dcecron';
    $cronStr = "* * * * * php -f ".checkSlash($config_options['client_dir_path'])."sugarclient/processAction.php ";
        $done = file_put_contents($cronPath,$cronStr);
    if(!$done) {
        // was 'Done'
        echo('ERROR::  failed writing Cron!, you will need to create cron with following entry ');
        die($cronStr);
    }else{
        //contents of cron are done, now set up the crontab to run    
        $cronscript = "crontab '".checkSlash($config_options['client_dir_path'])."sugarclient/dcecron'";
        shell_exec($cronscript);
    }


    //create the dce_config file
    require_once('dce_config.php');
    $config_options['client_archivePath']   = $arch_path;
    $config_options['client_instancePath']  = $web_path;
    $config_options['client_templatePath']  = $temp_path;
    $config_options['client_dbServer']      = $config_options['dce_dbServer'];
    $config_options['client_dbUser']        = $config_options['dce_dbUser'];
    $config_options['client_dbPass']        = $config_options['dce_dbPass'];

    //as last option change, reset client code and config si path  instead of pointing to root web path
    $config_options['client_dir_path']      = checkSlash($config_options['client_dir_path']).'sugarclient';
    $config_options['client_siPath']        = $config_options['client_dir_path'];
    
    //overwrite dce_config array 
    foreach($config_options as $k=>$v){
        $dce_config[$k] = $v;   
    }    

    //write or rewrite new config file
    $filePath = checkSlash($config_options['client_dir_path']).'dce_config.php';


    $config_string = '<?php
        // created: ' . date('Y-m-d H:i:s') .'
        global $dce_config;
        $dce_config = 
        '.var_export($dce_config, true) .';
        ?>';
        $done = file_put_contents($filePath,$config_string);

    if(!$done) {
        // was 'Done'
        die('ERROR::  failed writing DCE_Config! ');
    }

    

    require_once('db.php');
    //connection test
    //create db object
    $db = new DB();

    //declare information for connection     
    $db->server = $dce_config['dce_dbServer'];
    $db->user = $dce_config['dce_dbUser'];
    $db->password = $dce_config['dce_dbPass'];
    $db->database= $dce_config['dce_dbName'];

    //connect to DCE DB
    $db->connect();
    $db->close();    
?>

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
/*
 * Created on Feb 6, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once('dce_config.php');




function reportError($action_id, $inst, $db, $errString='', $filenames='',$parms=''){
    global $dce_config, $client_name,$nl;
    $logfiles = '';

    //retrieve logs to send back with error message
    if(!empty($errString)){
        actionLog($errString);
        $logfiles .= "$nl $errString $nl";
    }

    if(!empty($filenames)){
        if(!in_array('processAction.log',$filenames) ){
            $filenames[] = 'processAction.log';
        }
        foreach($filenames as $filename){
            if (file_exists($filename))
            {
                $logfiles .= "$nl --------- ( begin $filename ) --------$nl";
                $logfiles .= file_get_contents($filename);
                $logfiles .= "$nl --------- ( end $filename ) --------$nl";
            }
        }
    }
        if(!empty($parms)) $parms .=',';
        if(!empty($inst)){
            if (isset($inst['name'])) $parms .="inst_name:".$inst['name'].",";
            if (isset($inst['url'])) $parms .="site_url:".$inst['url'].",";
            if (isset($inst['admin_pass'])) $parms .="site_pass:".$inst['admin_pass'].",";
            if (isset($inst['licensed_expire'])) $parms .="licensed_expire:".$inst['licensed_expire'].",";
        }
        $parms .="instance_path:".checkSlash($dce_config['client_instancePath']) .$inst['name'].",";


    //update action with failed status
    $act_up_qry  = "UPDATE dceactions SET status = 'failed' ";
    if(!empty($logfiles)){$act_up_qry .= ", logs = '$logfiles' ";}
    $act_up_qry .= ", client_name= '$client_name' ";
    if(!empty($parms)){$act_up_qry .= ", cleanup_parms= '$parms' ";}
    $act_up_qry .= " WHERE id= '".$action_id."' ";

    $db->query($act_up_qry);



}


function updateDCEAction($inst, $action_id, $db, $filenames='', $parms='', $report=false){
    global $dce_config, $client_name, $singleActionLog,$nl;


    $logfiles = '';

    if(!empty($singleActionLog)){
        $logfiles .= "$nl --------- ( The following output was collected while running this action ) --------$nl";
        $logfiles .= addslashes($singleActionLog);
        $logfiles .= "$nl --------- ( end of output ) --------$nl";

    }

    if(!$report){

        if(!empty($filenames)){
            foreach($filenames as $filename){
               if (file_exists($filename))
                {
                    $logfiles .= "$nl --------- ( begin ".addslashes($filename)." ) --------$nl";
                    $content = file_get_contents($filename);
                    $logfiles .= addslashes($content);
                    $logfiles .= "$nl --------- ( end ".addslashes($filename)." ) --------$nl";
                }
            }
        }
        //$logfiles = $db->escape($logfiles);
        // create instance parameters string to be returned for use during cleanup.

        if(!empty($parms)) $parms .=',';
        if(!empty($inst)){
            if (isset($inst['name'])) $parms .="inst_name:".$inst['name'].",";
            if (isset($inst['url'])) $parms .="site_url:".$inst['url'].",";
            if (isset($inst['admin_pass'])) $parms .="site_pass:".$inst['admin_pass'].",";
            if (isset($inst['licensed_expire'])) $parms .="licensed_expire:".$inst['licensed_expire'].",";
            if (isset($inst['db_user'])) $parms .="db_user:".$inst['db_user'].",";
        }
        $parms .="instance_path:".checkSlash($dce_config['client_instancePath']) .$inst['name'].",";


        // As part of same db connect, dump any relevant log info into ’logs’ column,
        // record any parameters we need to pass back for dce cleanup, and update status
        $act_up_qry  = "UPDATE dceactions SET status = 'done' ";
        if(!empty($logfiles)){$act_up_qry .= ", logs = '$logfiles' ";}
        $act_up_qry .= ", client_name= '$client_name' ";
        $act_up_qry .= ", cleanup_parms = '".$parms."' ";
        $act_up_qry .= " WHERE id= '".$action_id."' ";
    }else{
        //this is from a report action, just update the action as completed
        $act_up_qry  = "UPDATE dceactions SET status = 'completed' ";
        if(!empty($logfiles)){$act_up_qry .= ", logs = '$logfiles' ";}
        $act_up_qry .= ", client_name = '$client_name' ";
        $act_up_qry .= ", date_completed = '".gmdate("Y-m-d H:i:s")."' ";
        $act_up_qry .= " WHERE id= '".$action_id."' ";

    }
    $db->query($act_up_qry);

    return true;

}

    //copy and process the config_si file before calling silent installer
    function process_si($db='', $instance, $instanceDir, $dbArr = '', $uniqueKey = ''){
    global $dce_config,$singleActionLog, $nl;

        $randDB = createRandDBName($instance['name'],$db);
        //if rand db is empty, we error out
        if(empty($randDB)){
            return 'Unique Database Name could not be created after 30 tries';
        }

        $randomPass = createRandPass(5);
        //add variables to instance array for si processing
        $instance['client_dbServer'] = $dce_config['client_dbServer'];//hostname to use when connecting to dce
        $instance['client_dbUser'] = $dce_config['client_dbUser'];// db admin user
        $instance['client_dbPass'] = $dce_config['client_dbPass'];// db admin password
        $instance['site_pass'] = $randomPass;//site password
        $instance['site_url'] = getInstanceURL($db, $instance['name']);//site url
        $instance['instDBName'] = $randDB;//name of db and dbuser (must be 16 chars or less)

        if(!isset($instance['demo_data']) || empty($instance['demo_data'])){
            $instance['demo_data'] = 'yes';
        }else{
            $instance['demo_data'] = 'false';
        }



        $replace = array(
            'INST_NAME' => 'name',
            'DEMO_DATA' => 'demoData',
            'LIC_USERS' => 'licensed_users',
            'LIC_EXPIRATION' => 'license_expire',
            'LIC_KEY' => 'license_key',
            'LIC_OC' => 'license_oc',
            'DB_USER' => 'client_dbUser',
            'DB_PASS' => 'client_dbPass',
            'DB_SERVER' => 'client_dbServer',
            'SITE_PASS' => 'site_pass',
            'URL' => 'site_url',
            'INST_DB_NAME' =>'instDBName',

            );

        //if config file does not already exist, then copy our default config file over
        if(!file_exists($instanceDir .'/config_si.php')){
            shell_exec("cp -rfp '".checkSlash($dce_config['client_dir_path'])."config_si.php' '" . $instanceDir ."/config_si.php'");
        }



            foreach($replace as $k=>$v)
            {
                if(isset($instance[$v])){
                    file_replace($k, $instance[$v], $instanceDir .'/config_si.php');
                }
            }

        shell_exec("chmod 777 '" . $instanceDir ."' -R");
        $singleActionLog .=  "  calling silent install:  ".getInstanceURL($db, $instance['name'])."$nl";
            $success = silent_install(getInstanceURL($db, $instance['name']));
            if(empty($success) || $success === false){
                //if $success is not a boolean, then it is the error message.
                //print out warning and return error message
                $singleActionLog .=  "Could not invoke Silent Installer page.";
                return $success;
            }

        $singleActionLog .=  "  removing config_si $nl";
         shell_exec("rm '".$instanceDir ."/config_si.php'");

        //add db string to config.php
        if(!is_array($dbArr) || empty($dbArr)){
            //do nothing
        }else{
            //get string of dbs to use
            $dbArrString = getMultipleDBArr($dbArr,$randDB,$randomPass);
            file_replace("'dbconfig' =>", $dbArrString."  'dbconfig' =>" , $instanceDir .'/config.php');
        }


        //  add unique key to config.php.  This prepends the DCE key to the created instance key
        if(!empty($uniqueKey)){
            file_replace("'unique_key' => '", "'unique_key' => '".trim($uniqueKey).".", $instanceDir .'/config.php');
        }else{
            file_replace("'unique_key' => '", "'unique_key' => 'nodcekeyfound.", $instanceDir .'/config.php');
        }



        //match elements to elements to be stored in db
        $instance['admin_pass'] = $instance['site_pass'];
        $instance['url'] = $instance['site_url'] ;
        $instance['db_user'] = $instance['instDBName'];

         return $instance;
    }


function silent_install($url){
    global $singleActionLog,$nl;
    $si_results = "";
    $server_page = $url . "/install.php";

    $singleActionLog .=  "Installing SugarCRM located at: $server_page ...$nl";
    $fh = fopen( $server_page . "?goto=SilentInstall&cli=true", "r" );
     if($fh === false){return false;}

    while( !feof( $fh ) ){
        $si_results .= fread( $fh, 8192 );
    }

    $info = stream_get_meta_data($fh);
    fclose( $fh );

    // message in a bottle
    preg_match( '/<bottle>(.*)<\/bottle>/s', $si_results, $message );
    if( count( $message ) == 2 ){
        // success
        $singleActionLog .=  $message[1] ;
    }
    else {
        // failure
        preg_match( '/Exit (.*)/', $si_results, $message );
        if( count( $message ) == 2 ){
            $singleActionLog .=   "Error.  Most likely your configuration file is invalid.  Message returned was:$nl" ;
        }
        else if( $info['timed_out'] ){
            $singleActionLog .=   "Error.  Connection timed out!" ;
        }
        else {
            $singleActionLog .=   "Unknown error.  I don't know about this type of error message:$nl" ;
        }
        $singleActionLog .=   $si_results . "$nl" ;
        //exit( 1 );
        return $si_results;
    }
    return true;

}


//print a line to the log file
 function actionLog($entry) {
    global $mod_strings,$dce_config,$client_name;
    $nld = '
'.date("Y-m-d H:i:s").'...';
    $client_name = shell_exec('hostname ');
    $client_name = trim($client_name);

    $log = (checkSlash($dce_config['client_dir_path']).'processAction_'.$client_name.'.log');

    // create if not exists
    if(!file_exists($log)) {
        $fp = @fopen($log, 'w+'); // attempts to create file
        if(!is_resource($fp)) {
            die('could not create the process log file');
        }
    } else {
        //clear filesize cache
        clearstatcache();
        //grab size of file and check to see if it is 10MB or more)
        $size = filesize($log);
        if($size > 10000000){
            //if size is more than 10 MB, rename log file
            rename($log, $log.strtotime("now"));
            //now attempt to recreate file
            $fp = @fopen($log, 'w+'); // attempts to create file
            if(!is_resource($fp)) {
                die('could not create the process log file');
            }

        }else{
            //size of log is under 10MB, so open up file to write to
            $fp = @fopen($log, 'a+'); // write pointer at end of file
            if(!is_resource($fp)) {
                die('could not open/lock process log file');
            }
        }

    }

    if(@fwrite($fp, $nld.$entry) === false) {
        die('could not write to process log: '.$entry);
    }

    if(is_resource($fp)) {
        fclose($fp);
    }
}


//generate random string of n characters
function createRandPass($numChars=7){

    //chars to select from
    $charBKT = "abcdefghijklmnpqrstuvwxyz123456789ABCDEFGHIJKLMNPQRSTUVWXYZ";
    // seed the random number generator
    srand((double)microtime()*1000000);
    $password="";
    for ($i=0;$i<$numChars;$i++)  // loop and create password
                $password = $password . substr ($charBKT, rand() % strlen($charBKT), 1);

//BEGIN SUGARCRM flav=int ONLY
    $password="asdf";
//END SUGARCRM flav=int ONLY

    return $password;

}

//generate dbname
function createRandDBName($instName = '', $db=''){
    global $dce_config;
    $nameLen = 0;
    $dbname = '';

    if (empty($db)){
        //create db object
        $db = new DB();

        //declare information for connection
        $db->server = $dce_config['dce_dbServer'];
        $db->user = $dce_config['dce_dbUser'];
        $db->password = $dce_config['dce_dbPass'];
        $db->database= $dce_config['dce_dbName'];
        //connect to DCE DB
        $db->connect();
    }

    //this needs to be done in a loop until unique name is found,
    //will set a break point to avoid infinite loop
    $unique = false;
    $itr = 0;
    while ($unique === false){
        //grab the first 6 characters of name
        if(!empty($instName)){
            $dbname .= substr($instName, 0, 6);
        }
        //now add 5 random chars (total of 12)
        $dbname .= '_'.createRandPass(5);

        //query to see that this name is unique
        $chkNameQry = "select name from dceinstances where  name = '$dbname' and deleted = 0";
        $qid = $db->query($chkNameQry);

        //retrieve the count
        $returned = $db->num_rows($qid);

        $itr = $itr +1;
        if($returned < 1){
            $unique = true;
        }
        if($itr > 30){
            return '';
        }

    }

    return $dbname;
}

function checkSlash($dirSTR){
     if(substr($dirSTR, -1, 1) != '/'){
        $dirSTR .= '/';
    }
    return $dirSTR;

}

function checkHaystackFileForNeedle($haystack, $needle){
    //make sure haystack is a file
    if(!file_exists($haystack)){
        return false;
    }
    //get contents of the file to search
    $hay = file_get_contents($haystack);

    //return true if contents are found
    if(strpos($hay, $needle)!==false){
        return true;
    }



    return false;
}

function getUpgradeVars($db, $action, $inst){
    global $dce_config;
    //get destination template name

    //get target template from cleanup params
    $paramArr = retrieveParamsFromString($action['action_parms']);

    if(empty($paramArr['totemplate'])){return '';}

    //create and execute query for current Template
    $getTmpltQry = "select template_name, sugar_version, sugar_edition from dcetemplates where id = '".$action['template_id']."'";
    $qtd = $db->query($getTmpltQry);
    $currTmpl = $db->fetch_array($qtd);
    if(empty($currTmpl)){return '';}

    //get destination template path and url
    $upGPs =  getUpgradePath($db, $inst, $paramArr['totemplate'],$currTmpl['sugar_version'],$currTmpl['sugar_edition']);
    $upgradeArrs['destTempPath'] =  $upGPs['tmpl_path'];
    $upgradeArrs['destTempUpgradePath'] =  $upGPs['upgrade_dir'];//getUpgradePath($db, $action, $inst, $paramArr['totemplate']);
    $upgradeArrs['destTempURL'] =   $upGPs['tmpl_url'];

    //get instance path
    $upgradeArrs['instPath'] = checkSlash($dce_config['client_instancePath']) .$inst['name'];

    //get current template path and url
    $upgradeArrs['srcTempPath'] =  checkSlash($dce_config['client_templatePath']).$currTmpl['template_name'];
    $upgradeArrs['srcTempURL'] =   checkSlash($dce_config['client_Templ_URL']).$currTmpl['template_name'];

    //specify log path
    $upgradeArrs['logPath'] = $upgradeArrs['instPath']."/_silentupgrade_dryrun.log";

    if(isset($paramArr['clone_db']))
        $upgradeArrs['clone_db'] = $paramArr['clone_db'];

    if(isset($paramArr['delete_clone']))
        $upgradeArrs['delete_clone'] = $paramArr['delete_clone'];



    return $upgradeArrs;
}

    function getUpgradePath($db, $inst, $templ_id, $sugar_version, $sugar_edition){
        global $dce_config, $singleActionLog;
        $manifest = array();
        $tmplInfo = array();

        //start by retrieving the destination template
        $getTmpltQry = "select template_name, sugar_version, sugar_edition from dcetemplates where id = '".$templ_id."'";
        $qtd = $db->query($getTmpltQry);
        $tmpl = $db->fetch_array($qtd);
        if(empty($tmpl)){

             //Root Directory is invalid
             $singleActionLog .= ' Template with id \''.$templ_id.'\' could not be retrieved ';
             echo $singleActionLog ;
             return false;


            }


        //get path to upgrade directory in template
        $tmplInfo['tmpl_path'] = checkSlash($dce_config['client_templatePath']) . $tmpl['template_name'];
        $upgrade_dir = checkSlash($tmplInfo['tmpl_path']).'DCEUpgrade';
        //get url to template
        $tmplInfo['tmpl_url'] = checkSlash($dce_config['client_Templ_URL']).$tmpl['template_name'];
        //error if not found
        if(empty($upgrade_dir) || !file_exists($upgrade_dir)){
             //Root Directory is invalid
             $singleActionLog .= ' Upgrade Directory does not exist  ';
             echo $singleActionLog ;
             return false;
        }

        if(!is_dir("$upgrade_dir")){
             //Upgrade Directory is invalid
             $singleActionLog  .= ' input is not a directory  ';
             echo $singleActionLog ;
             return false;
        }

        $handle = opendir("$upgrade_dir");

        //loop through the root directory for each subdirectory (Instance root directory)
        $singleActionLog  .= '  searching for manifest.php by looping through '.$upgrade_dir;
        while (false !== ($dir = readdir($handle))) {


            //set/reset the manifest array
            $manifest = array();
            //make sure you go into directory tree and not out of tree
            if($dir!= '.' && $dir!= '..'){
                //retrieve file for this directory
                $fileArr = scandir(checkSlash($upgrade_dir).$dir);


                //check for manifest file
                if(!in_array('manifest.php',$fileArr)){
                    //no manifest, cannot process, move on to next dir
                    continue;
                }else{
                    //grab manifest file
                    require_once(checkSlash($upgrade_dir).$dir.'/manifest.php');



                  $singleActionLog  .= 'searching for manifest.php by looping through '.$upgrade_dir;

                    $flavor_ok = false;
                    $version_ok = false;
                  //parse manifest file to see if versions match up
                    if(isset($manifest['acceptable_sugar_versions'])) {

                        if(isset($manifest['acceptable_sugar_versions']['exact_matches'])) {

                            foreach($manifest['acceptable_sugar_versions']['exact_matches'] as $match) {
                                if($match == $sugar_version) {
                                    $version_ok = true;
                                }
                            }
                        }
                        if(!$version_ok && isset($manifest['acceptable_sugar_versions']['regex_matches'])) {

                            foreach($manifest['acceptable_sugar_versions']['regex_matches'] as $match) {
                                if(preg_match("/$match/", $sugar_version)) {
                                    $version_ok = true;
                                }
                            }
                        }
                    }


                  //parse manifest file to see if flavors match up
                    if(isset($manifest['acceptable_sugar_flavors'])) {
                        foreach($manifest['acceptable_sugar_flavors']as $fl_match) {
                            if($fl_match == $sugar_edition) {
                                $flavor_ok = true;
                            }
                        }
                    }


                    //match found, return this path
                    if($version_ok === true && $flavor_ok === true){
                        $tmplInfo['upgrade_dir'] = checkSlash($upgrade_dir).$dir;
                        return $tmplInfo;
                    }

                }

            }
          }




        return false;
    }

    function returnInstanceDB($instName){
        global $dce_config, $singleActionLog,$nl;

        //import the intance sugarconfig file and grab db info
        $inst_path = checkSlash($dce_config['client_instancePath']) . $instName;
        if (file_exists($inst_path.'/config.php')){
            require($inst_path.'/config.php');
            $dbInfo = $sugar_config['dbconfig'];

            //declare information for connection
            $instDB = new DB();
            $instDB->server = $dbInfo['db_host_name'];
            $instDB->user = $dbInfo['db_user_name'];
            $instDB->password = $dbInfo['db_password'];
            $instDB->database= $dbInfo['db_name'];
            //connect to DCE DB
            $instDB->connect(true);
        }else{
            $singleActionLog .= "$nl could not run reports on instance because config file did not exist here :".$inst_path.'/config.php';
        }

        return $instDB;
    }

    function returnTimeRanges(){
        global $timedate;
         //change time into timestamp
        $now = $timedate->nowDb();

        //convert back into date format
        $yesterday = $timedate->getNow()->get('-1 day')->asDb();

        //create the 24 hour ranges
        $hour['range1']['start'] = $yesterday.'0:00:00';
        $hour['range1']['end']   = $yesterday.'0:59:59';
        $hour['range2']['start'] = $yesterday.'1:00:00';
        $hour['range2']['end']   = $yesterday.'1:59:59';
        $hour['range3']['start'] = $yesterday.'2:00:00';
        $hour['range3']['end']   = $yesterday.'2:59:59';
        $hour['range4']['start'] = $yesterday.'3:00:00';
        $hour['range4']['end']   = $yesterday.'3:59:59';
        $hour['range5']['start'] = $yesterday.'4:00:00';
        $hour['range5']['end']   = $yesterday.'4:59:59';
        $hour['range6']['start'] = $yesterday.'5:00:00';
        $hour['range6']['end']   = $yesterday.'5:59:59';
        $hour['range7']['start'] = $yesterday.'6:00:00';
        $hour['range7']['end']   = $yesterday.'6:59:59';
        $hour['range8']['start'] = $yesterday.'7:00:00';
        $hour['range8']['end']   = $yesterday.'7:59:59';
        $hour['range9']['start'] = $yesterday.'8:00:00';
        $hour['range9']['end']   = $yesterday.'8:59:59';
        $hour['range10']['start'] = $yesterday.'9:00:00';
        $hour['range10']['end']   = $yesterday.'9:59:59';
        $hour['range11']['start'] = $yesterday.'10:00:00';
        $hour['range11']['end']   = $yesterday.'10:59:59';
        $hour['range12']['start'] = $yesterday.'11:00:00';
        $hour['range12']['end']   = $yesterday.'11:59:59';
        $hour['range13']['start'] = $yesterday.'12:00:00';
        $hour['range13']['end']   = $yesterday.'12:59:59';
        $hour['range14']['start'] = $yesterday.'13:00:00';
        $hour['range14']['end']   = $yesterday.'13:59:59';
        $hour['range15']['start'] = $yesterday.'14:00:00';
        $hour['range15']['end']   = $yesterday.'14:59:59';
        $hour['range16']['start'] = $yesterday.'15:00:00';
        $hour['range16']['end']   = $yesterday.'15:59:59';
        $hour['range17']['start'] = $yesterday.'16:00:00';
        $hour['range17']['end']   = $yesterday.'16:59:59';
        $hour['range18']['start'] = $yesterday.'17:00:00';
        $hour['range18']['end']   = $yesterday.'17:59:59';
        $hour['range19']['start'] = $yesterday.'18:00:00';
        $hour['range19']['end']   = $yesterday.'18:59:59';
        $hour['range20']['start'] = $yesterday.'19:00:00';
        $hour['range20']['end']   = $yesterday.'19:59:59';
        $hour['range21']['start'] = $yesterday.'20:00:00';
        $hour['range21']['end']   = $yesterday.'20:59:59';
        $hour['range22']['start'] = $yesterday.'21:00:00';
        $hour['range22']['end']   = $yesterday.'21:59:59';
        $hour['range23']['start'] = $yesterday.'22:00:00';
        $hour['range23']['end']   = $yesterday.'22:59:59';
        $hour['range24']['start'] = $yesterday.'23:00:00';
        $hour['range24']['end']   = $yesterday.'23:59:59';

        return $hour;
    }




    function file_replace ($search, $replace, $filename) {
        if (file_exists($filename))
        {
            $cnt = file_get_contents($filename);
            if (strstr($cnt, $search))
            {
                $cnt = str_replace($search, $replace, $cnt);
                return file_put_contents($filename, $cnt);
            }
            return true;
        }
        return false;
    }


    /**
     * lifted from utils.php, but used on DN side
     */
    function createGuidOnDN()
    {
        $microTime = microtime();
        list($a_dec, $a_sec) = explode(" ", $microTime);

        $dec_hex = dechex($a_dec* 1000000);
        $sec_hex = dechex($a_sec);

        ensure_length($dec_hex, 5);
        ensure_length($sec_hex, 6);

        $guid = "";
        $guid .= $dec_hex;
        $guid .= createGuidSection(3);
        $guid .= '-';
        $guid .= createGuidSection(4);
        $guid .= '-';
        $guid .= createGuidSection(4);
        $guid .= '-';
        $guid .= createGuidSection(4);
        $guid .= '-';
        $guid .= $sec_hex;
        $guid .= createGuidSection(6);

        return $guid;

    }

    function createGuidSection($characters)
    {
        $return = "";
        for($i=0; $i<$characters; $i++)
        {
            $return .= dechex(mt_rand(0,15));
        }
        return $return;
    }

    function ensure_length(&$string, $length)
    {
        $strlen = strlen($string);
        if($strlen < $length)
        {
            $string = str_pad($string,$length,"0");
        }
        else if($strlen > $length)
        {
            $string = substr($string, 0, $length);
        }
    }

    function getMultipleDBArr($dbArr,$DB='',$Pass=''){
        global $singleActionLog,$nl;
        $DBUsr = $DB;
        $dbRetStr = '';

        if(empty($dbArr)) return $dbRetStr;

        $dbRetStr = "'db'=>array( ";
            foreach ($dbArr as $dbInfo){
                if (empty($dbInfo)) continue;

               if(!empty($dbInfo['user_name'])){
                    //$DB = $dbInfo['name'];
                    $DBUsr = $dbInfo['user_name'];
               }
               if(!empty($dbInfo['user_pass'])){
                    $Pass = $dbInfo['user_pass'];
               }

                if($dbInfo['list_role'] == 1){
                    $dbRetStr .="
                    'listviews'=>array(
                       'db_host_name' => '".$dbInfo['host']."',
                       'db_host_instance' => '',
                       'db_user_name' => '".$DBUsr."',
                       'db_password' => '".$Pass."',
                       'db_name' => '".$DB."',
                       'db_type' => 'mysql',
                    ), ";
                }


                if($dbInfo['reports_role'] == 1){
                    $dbRetStr .="
                    'reports'=>array(
                       'db_host_name' => '".$dbInfo['host']."',
                       'db_host_instance' => '',
                       'db_user_name' => '".$DBUsr."',
                       'db_password' => '".$Pass."',
                       'db_name' => '".$DB."',
                       'db_type' => 'mysql',
                    ), ";
                }


           }
          $dbRetStr .= "),";
           $singleActionLog .=  "database string is $dbRetStr $nl";
           return $dbRetStr;
    }


    function retrieveParamsFromString($pStr=''){
        if(empty($pStr)){
            return $pStr;
        }

        $param_sects = explode(',', $pStr);
        $parms = array();
        foreach($param_sects as $ps){
            if(!empty($ps)){
                $pos = strpos($ps,':');
                if($pos !== false && $pos > 0){
                    $k = substr($ps, 0, $pos);
                    $v = substr($ps, $pos+1);
                    $parms[trim($k)]= ($v);
                }
            }
        }
        return $parms;
    }



function getInstanceURL($db = '', $instance_name){
    global $dce_config;

    if (empty($db)){
        //create db object
        $db = new DB();

        //declare information for connection
        $db->server = $dce_config['dce_dbServer'];
        $db->user = $dce_config['dce_dbUser'];
        $db->password = $dce_config['dce_dbPass'];
        $db->database= $dce_config['dce_dbName'];
        //connect to DCE DB
        $db->connect();

    }

    //create and execute query for queued actions
    $getActionQry = "select url_format from dceclusters where  id = '".$dce_config['client_cluster_id']."' and deleted = 0";
    $qid = $db->query($getActionQry);

    //just grab first row
    $row = $db->fetch_array($qid);
    $pattern = $row['url_format'];
    if(empty($pattern))  $pattern = 'URL/Instance_Name';


    if($pattern == 'URL/Instance_Name'){
        //return url as is
        return checkSlash($dce_config['client_baseURL']) . $instance_name;//site url;
    }else{
        //parse url and reformat to be (Instance_name.hostname)
        $url = checkSlash($dce_config['client_baseURL']);
        $new_url = str_replace('www','',$url);
        $new_url = str_replace('http://','',$new_url);
        return  'http://'.trim($instance_name).'.'.trim($new_url);

    }
}


    function changeFilePerms($filepath, $recursive = true, $mode='', $modOrGrp='', $owner=''){
        global $singleActionLog, $dce_config, $nl;
         $sprintStr ='';

         //make sure filepath is not empty
         if (empty($filepath) || empty($mode)){
            return false;
         }
         //set recursive option
         if($recursive){
            $recursive = '-R';
         }else{
            $recursive = ' ';
         }

         //$mode can be either both, chmod, chown
         if ($mode =='chmod'){
            if(empty($modeOrGrp)){
                 $modeOrGrp = '755';
            }

            $singleActionLog .=  " changing permissions back on directory, we are done. $nl";
            $perms_script = " chmod $modeOrGrp  '$filepath' $recursive ";
            //note we are not yet using php chmod because we need the call to be recursive.
            $sprintStr .= $nl . shell_exec($perms_script);
         }


         if ($mode =='chown'){
            if(empty($modeOrGrp)&& isset($dce_config['client_cluster_group'])  && !empty($dce_config['client_cluster_group'])){
                 $modeOrGrp =   $dce_config['client_cluster_group'];
            }

            $singleActionLog .=  " changing group $nl";
            $sprintStr .=  shell_exec("chgrp $recursive $modeOrGrp  '$filepath'");


            if(empty($owner)&& isset($dce_config['client_cluster_user'])  && !empty($dce_config['client_cluster_user'])){
                 $owner =   $dce_config['client_cluster_user'];
            }
            if(!empty($owner)){
                $singleActionLog .=  " changing group $nl";
                $sprintStr .=  shell_exec("chown $recursive  $owner '$filepath'");
            }
         }

     return $sprintStr;
    }

    function loadCreateSQLScripts($instDBName,$tempPath){
        global $dce_config, $singleActionLog, $nl;
        //set the paths we will be using for loading the dump
        $dbPath = '';
        if(!empty($dce_config['client_mysql_path'])){
            $dbPath = checkSlash($dce_config['client_mysql_path']);
        }
        $tmpl_sql_path = checkSlash($tempPath).'sqlLoad';

        //create the record to insert into db
        $singleActionLog =  " processing SQL load scripts .. ";

        //loop through sql directory and process scripts
        $scriptFound = false;

        //check to see if directory exists
        if(file_exists($tmpl_sql_path) && is_dir($tmpl_sql_path)){
            //grab all the files in the directory and loop through them
            $handle = opendir("$tmpl_sql_path");
            //loop over the directory and go into each child directory
            while (false !== ($file = readdir($handle))) {
              //make sure you go into directory tree and not out of tree, and this is a sql file
              $loadSQL = '';
              $path_parts = '';
              $path_parts = pathinfo($file);





              if($file!= '.' && $file!= '..' && is_file($file) && $path_parts['extension'] =='sql'){
                //if sql script, load into mysql
                $scriptFound = true;
                $loadSQL = $dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer']."  ".$instDBName." < ".$tmpl_sql_path ."/".$file ;
                $singleActionLog .= $nl . "----processing script $loadSQL---";
                $singleActionLog .= $nl . shell_exec($loadSQL);
              }
            }



            //directory found but no scripts found
            if($scriptFound){
                $singleActionLog .=  " No SQL scripts found.. ";
            }
        }else{
            //directory not found
            $singleActionLog .=  " SQL scripts directory not found.. ";
        }

        //close db connection
        $singleActionLog .=  " Finished processing SQL scripts.. ";
    }
?>

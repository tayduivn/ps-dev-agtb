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

//change directories to where this file is located.
//this is to make sure it can find dce_config.php
chdir(realpath(dirname(__FILE__)));

  require_once('db.php');
  require_once('client_utils.php');
  require_once('dce_config.php');
  global $client_name, $singleActionLog, $nl;
  $nl='
';

  $singleActionLog .= $nl;
 //first check to see this ip is in array of active clients.  If not, abort job now.

    //get client Name
    $client_name = shell_exec('hostname ');
    $client_name = trim($client_name);
    //get list of active IP's
    $an = $dce_config['active_clients'];

    $singleActionLog .= "{$nl}processing ip: $client_name";
    //make sure ip is active
    if(!in_array($client_name, $an)){
     actionLog("aborting job on $client_name, DCE Client is not active");
     return false;
    }



     // get the number of jobs to process and iterate
    $timesToIterate = $dce_config['job_per_call'];
    $timesIterated = 0;
    $singleActionLog .= "{$nl}times to iterate is $timesToIterate";
    while($timesIterated<$timesToIterate){
        $timesIterated = $timesIterated + 1;
        $singleActionLog .= "{$nl}iteration #$timesIterated";

        //create db object
        $db = new DB();

        //declare information for connection
        $db->server = $dce_config['dce_dbServer'];
        $db->user = $dce_config['dce_dbUser'];
        $db->password = $dce_config['dce_dbPass'];
        $db->database= $dce_config['dce_dbName'];

        //connect to DCE DB
        $db->connect();

        //create and execute query for queued actions
        $getActionQry = "select * from dceactions where status = 'queued' and cluster_id = '".$dce_config['client_cluster_id']."' and deleted = 0 and priority != '-1' order by priority desc, date_entered asc, start_date asc";
        $qid = $db->query($getActionQry);

        //just grab first row
        $row = $db->fetch_array($qid);
        $confRow = '';
        $excludeIDs = '';
        $startDate = '';


        //lets make sure start date is ok, lets grab the start_date
        if(!empty($row)){
            $startDate = $row['start_date'];
        }
        $currDate = TimeDate::getInstance()->nowDb();

        //compare start date with current time in a loop
        while(!empty($row) && ($startDate > $currDate)){
            $singleActionLog .=  "skipping action record #".$row['id'].", date to execute is in future";
            //if the start date is for a time in the future, then add id to list of excluded id's
            if(empty($excludeIDs)){
                $excludeIDs .= "'".$row['id']."'";
            }else{
                $excludeIDs .= " , '".$row['id']."'";
            }
            //requery for the next available row
            $ActionReQry = "select * from dceactions where status = 'queued' and cluster_id = '".$dce_config['client_cluster_id']."' and deleted = 0 and id not in($excludeIDs)  order by priority desc,  date_entered asc, start_date asc";
            $rqid = $db->query($ActionReQry);
            $row = $db->fetch_array($rqid);

            //grab the new start date and current date
            if(!empty($row)){
                $startDate = $row['start_date'];
            }
                $currDate = gmdate('Y-m-d H:i:s');
        }

        if(empty($row)){
            //no actions pending
            $db->close();
            continue;
        }else{

            //change the action status to show it has been taken.  Check to see it has not already been
            //taken by another node first to avoid conflicts.
            $takeActionQry = "update dceactions set status = 'started', client_name = '$client_name', date_started='".gmdate('Y-m-d H:i:s')."' where id = '".$row['id']."' and status = 'queued'";
            $db->query($takeActionQry);

            //now confirm we have this job
            $confirmActionQry = "select * from dceactions where status = 'started' and cluster_id = '".$dce_config['client_cluster_id']."'  and id = '".$row['id']."' and deleted = 0";

            $cid = $db->query($confirmActionQry);
            //just grab first row
            $confRow = $db->fetch_array($cid);

        }

        //call right action steps, based on type
        if(empty($confRow) || empty($confRow['type'])){
            actionLog('could not get exclusive lock on action record: '.$row['id']);
            continue;
        }

        //create and execute query for Action Instance
        $getInstQry = "select * from dceinstances where id = '".$confRow['instance_id']."'";
        $qid = $db->query($getInstQry);
        $inst = $db->fetch_array($qid);
        $singleActionLog .=  "processing action record: ".$confRow['id'];

        switch($confRow['type']){
            case 'create':
            if(empty($inst->parent_dceinstance_id)){
                //this is a create action and parent id is empty so create instance
                createInstance($db, $confRow,$inst);
            }else{
                //this is a create action and parent id is not empty so clone instance
                cloneInstance($db, $confRow,$inst);
            }
            break;

            case 'convert':
            if ($inst['from_copy_template'] ==0)
                convertInstance($db, $confRow,$inst);
             break;

            case 'clone':
             if ($inst['from_copy_template'] ==0)
                cloneInstance($db, $confRow,$inst);
             break;

            case 'archive':
             archiveInstance($db, $confRow,$inst);
             break;

            case 'delete':
             deleteInstance($db, $confRow,$inst);
             break;

            case 'recover':
             recoverInstance($db, $confRow,$inst);
             break;

            case 'toggle_on':
             toggleUserOn($db, $confRow,$inst);
             break;

            case 'toggle_off':
             toggleUserOff($db, $confRow,$inst);
             break;

            case 'upgrade_test':
             if ($inst['from_copy_template'] ==0)
                upgradeInstance($db, $confRow,$inst, true, false);
             break;

            case 'upgrade_live':
             if ($inst['from_copy_template'] ==0)
                upgradeInstance($db, $confRow,$inst,false, true);
             break;

            case 'report':
              if ($inst['from_copy_template'] ==0)
                gatherReportData($db, $confRow,$inst);
             break;

            case 'import':
             importInstances($db, $confRow,$inst);
             break;

            case 'license':
             updateLicense($db, $confRow,$inst);
             break;

            case 'key':
             updateKey($db, $confRow,$inst);
             break;

            default:
             actionLog('could not resolve action type: '.$confRow['type']);
             break;
        }

        //close db connection
        $db->close();
        //clear single action log
        $singleActionLog .=  "";
  }


  function createInstance($db, $action, $inst){
    require_once('createInstance.php');
    global $dce_config, $singleActionLog,$nl;
    $sprintStr ='';

     $singleActionLog .=  "{$nl}Using Instance ----";
     $singleActionLog .=  var_export($inst, false);
     $singleActionLog .=  $nl;

    //create and execute query for Action Template
    $getTmpltQry = "select template_name from dcetemplates where id = '".$action['template_id']."'";
    $qtd = $db->query($getTmpltQry);
    $Tmpl = $db->fetch_array($qtd);
     $singleActionLog .=  "{$nl}Using Template ----";
     $singleActionLog .=  var_export($Tmpl, false);
     $singleActionLog .=  $nl;


    if(empty($Tmpl) || empty($inst)){
        $errString = "Action with id: ".$action['id']." could not be completed...  ";
        if(empty($Tmpl))$errString .= 'The template record could not be found for this action.';
        if(empty($inst))$errString .= 'The instance record could not be found for this action.';
        reportError($action['id'], $inst, $db, $errString);
        return false;
    }

    //get any related databases for processing during install
    $getDBQry = "select * from dcedatabases where cluster_id = '".$dce_config['client_cluster_id']."' and deleted = 0";
    $dbq = $db->query($getDBQry);
    while(($dbArr[] = $db->fetch_array($dbq))!= null)
     $singleActionLog .=  "{$nl}Related DB's ----";
     $singleActionLog .=  var_export($dbArr, false);
     $singleActionLog .=  $nl;



    // process new instance to be created
     $singleActionLog .=  " processing new instance $nl";
    $inst_path = checkSlash($dce_config['client_instancePath']) .$inst['name'];
    $temp_path = checkSlash($dce_config['client_templatePath']).$Tmpl['template_name'].'/';
    $temp_url = checkSlash($dce_config['client_Templ_URL']).$Tmpl['template_name'].'/';

    //make sure that the instance path does not exist already
    $instExists = file_exists($inst_path);
    //make sure the template exists in path
    $tempExists = file_exists($temp_path);

   //give error message if temp does not exist, or instance already exists
    if($instExists || !$tempExists){
        $errString = "Action with id: ".$action['id']." could not be completed...  ";
        if($instExists)  $errString .= 'Instance Directory already exists on local filesystem ';
        if(!$tempExists) $errString .= 'The template directory does not exist on local filesystem';
        reportError($action['id'], $inst, $db, $errString);
        return false;
    }

    //call method that copies over instance directory
    $singleActionLog .=  " copying over instance directory ";
    $success = process_create_instance($temp_path, $inst_path, $temp_url, $dce_config['client_dir_path'] );

    //make sure the instance was created with ini setup file
    $instExists = file_exists($inst_path);
    $iniExists = file_exists($inst_path . '/ini_setup.php');


   //give error message if files were not created
    if(!$success || !$instExists || !$iniExists){
        //create error string
        $errString = "Action with id: ".$action['id']." could not be completed...  ";
        if(!$instExists)  $errString .= 'Instance Directory was not created succesfully on filesystem ';
        if(!$iniExists) $errString .= 'The instance directory was created, but ini_setup.php file could not be created on local filesystem ';
        if(!$iniExists) $errString .= 'Will roll back changes made to file system.  ';

        //remove directory if created
        if($instExists) shell_exec("rm -rf '".$inst_path."'");

        reportError($action['id'], $inst, $db, $errString);
        return false;
    }


    //Change permissions on files as needed.
     $singleActionLog .=  " changing permissions while we work on directory $nl";
    $perms_script = sprintf($perms = "chmod 777 '%s' -R ",$inst_path);
    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);


    //Parse si_config file to populate for silent install. Will take in info from instance record such as
    //lic key, instance name, etc. and Execute Silent Installer.
    $singleActionLog .=  " processing si $nl";
    $action_params = retrieveParamsFromString($action['action_parms']);
    $uneek = '';
    if(isset($action_params['unique_key'])){$uneek = $action_params['unique_key'];}
    $installRet = process_si($db, $inst, $inst_path, $dbArr, $uneek);

    //check to see if return value is a string, if so, then install
    //failed and string is the error message
    if(empty($installRet) || $installRet === false){
        //create error string
        $errString = 'Action with id: '.$action['id'].' could not be completed...
        Instance '.$inst['name'] .' could not be installed, below is the install output:
        '.$installRet.'
        Will roll back changes made to file system.  ';

        //call delete to remove the directory and db entries
        deleteCore($db, $action,$inst,false);
        //report the error
        reportError($action['id'], $inst, $db, $errString);
        return false;
    }else{
        //assign returned object to be Instance
        $inst = $installRet;
    }


    //change permissions back to 755 from 777
    $singleActionLog .=  " changing permissions back on directory, we are done. $nl";
    $perms_script = sprintf($perms = "chmod 755 '%s' -R ",$inst_path);

    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);


    //change User/Group ownership(if specified).
    if(isset($dce_config['client_cluster_group'])  && !empty($dce_config['client_cluster_group'])){
        $singleActionLog .=  " changing group $nl";
        $sprintStr .= $nl . shell_exec("chgrp -R ". $dce_config['client_cluster_group'] ." '".$inst_path."'");
    }
    if(isset($dce_config['client_cluster_user'])  && !empty($dce_config['client_cluster_user'])){
        $singleActionLog .=  " changing owner $nl";
        $sprintStr .= $nl . shell_exec("chown -R ".$dce_config['client_cluster_user'] ." '".$inst_path."'");
    }

    //load db with any sql scripts if they are included in template
    loadCreateSQLScripts($inst['db_user'],$temp_path);

    //if there were any errors, send out an error message
    $sprintStr = trim($sprintStr);
    if(empty($sprintStr)){
        // No errors, Connect to DCE DB and Update actions record to have status of ’Done’.
            $singleActionLog .=  " updating actions table. $nl";
            $filenames[] = $inst_path.'/install.log';
            $filenames[] = $inst_path.'/sugarcrm.log';
            updateDCEAction($inst, $action['id'], $db, $filenames);

        $singleActionLog .=  " done!!!! $nl";
    }else{
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $sprintStr;
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;
    }
  }


  function convertInstance($db, $action, $inst){
    global $dce_config, $singleActionLog;
/*
This is here as a hook, for future use.  Currently no client action needs to occur
*/


  }


  function cloneInstance($db,$action,$inst){
    global $dce_config, $singleActionLog;

    //retrieve parent instance
    //create and execute query for Action Instance
    $singleActionLog .=  "executing cloneInstance";
    $getParInstQry = "select name from dceinstances where id = '".$inst['parent_dceinstance_id']."'";
    $pid = $db->query($getParInstQry);
    $parent_inst = $db->fetch_array($pid);

    //make sure parent instance exists
    $inst_path = checkSlash($dce_config['client_instancePath']) . $inst['name'];
    $par_inst_path = checkSlash($dce_config['client_instancePath']) . $parent_inst['name'];
    if(!file_exists($par_inst_path)){
        $errString = 'Clone Action with record '.$action['id'].' could not be completed, parent instance directory could not be found.  ';
        reportError($action['id'], $inst, $db, $errString);
        return false;
    }

    //call core clone code
    $retArr = cloneCore($db,$inst,$parent_inst,$action['action_parms']);
    $filenames = $retArr['filenames'];
    $inst = $retArr['inst'];

    //if there were any errors, send out an error message
    if(!isset($filenames['sprintStr'])) $filenames['sprintStr'] = '';

    $filenames['sprintStr'] = trim($filenames['sprintStr']);
    if(empty($filenames['sprintStr'])){
        // No errors, Connect to DCE DB and Update actions record to have status of ’Done’.
            $singleActionLog .=  " updating actions table\n";
            updateDCEAction($inst, $action['id'], $db, $filenames);

        $singleActionLog .=  " done!!!!\n";
    }else{
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $filenames['sprintStr'];
        //remove files and database
        deleteCore($db, $action,$inst,false);
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors\n";
        return false;

    }

  }


  function cloneCore($db,$inst,$parent_inst,$parms=''){
    global $dce_config, $singleActionLog, $nl;
    $sprintStr ='';
    $dbPath = '';
    if(!empty($dce_config['client_mysql_path'])){$dbPath = checkSlash($dce_config['client_mysql_path']);}
    $inst_path = checkSlash($dce_config['client_instancePath']) . $inst['name'];
    $par_inst_path = checkSlash($dce_config['client_instancePath']) . $parent_inst['name'];

    //copy files from parent to new directory
    $singleActionLog .=  " copying files to new directory $nl";
    $cp_script = sprintf("cp -rf '%s' '%s' ",$par_inst_path, $inst_path);
    //note we are not yet using php copy() because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($cp_script);

    //if the file was not copied over, then return error message.
    if(!file_exists($inst_path)){
        $filenames['sprintStr'] = 'files could not be copied over from '. $par_inst_path.' to '. $inst_path.'
                error is: '.$cp_script;
        return $filenames;
    }

    //change ownership and permissions
    $singleActionLog .=  " changing permissions while we work on directory $nl";
    $perms_script = sprintf($perms = "chmod 777 '%s' -R ",$inst_path);
    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);


    //lets change ini_setup.php to point to new directory
    $singleActionLog .=  " rewriting ini_setup.php and .htaccess to point to new directory";
    file_replace($parent_inst['name'], $inst['name'], $inst_path .'/ini_setup.php');
    file_replace($parent_inst['name'], $inst['name'], $inst_path .'/.htaccess');


    //grab any action params
     $action_params = retrieveParamsFromString($parms);
     $singleActionLog .=  " retrieved the following action params$nl";
     $singleActionLog .=  var_export($action_params, false);

    //if cloneDB flag checked, copy the db
    echo"
            from clonecore, params are ".print_r($action_params)."
    ";
    if(isset($action_params['clone_db']) && $action_params['clone_db']){
        $inst['db_user'] .= '_c';
        $singleActionLog .=  "clone db flag is checked, so clone parent db$nl";
        //replace the name of old instance info in config.php
        file_replace($parent_inst['name'], $inst['name'], $inst_path .'/config.php');

        //dump db
        $getDBDump = $dbPath."mysqldump -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer']."  ".$parent_inst['db_user']." > ".$inst_path ."/dump.sql" ;
        $sprintStr .= $nl . shell_exec($getDBDump);

        //create new db
        $newDB = "echo    \"create database ". $inst['db_user']." \" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
        $sprintStr .= $nl . shell_exec($newDB);

        //create new db user
        $newUSR = "echo    \" grant all on ".$inst['db_user'].".* to '".$inst['db_user']."'@'localhost' identified by '".$inst['db_user']."'\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
        shell_exec($newUSR);

        //load db
        $getDBDump = $dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer']."  ".$inst['db_user']." < ".$inst_path ."/dump.sql" ;
        $sprintStr .= $nl . shell_exec($getDBDump);

        //point the db to new one in confg.php
        file_replace($parent_inst['name'], $inst['name'], $inst_path .'/.config.php');

        $filenames[] = 'processLog.php';

    }else{
        $singleActionLog .=  " Clone DB flag was NOT set, so run install$nl";
        //if cloneDB flag is not checked, remove config, parse si and run silent install
        if(file_exists($inst_path .'/config.php')){
            $singleActionLog .=  " drop existing config.php $nl";
            //dropping existing config file
            unlink($inst_path .'/config.php');
        }
        //recreate config to be 0 length
        $singleActionLog .=  " recreate config.php with 0 file size$nl";
        $handle = fopen($inst_path .'/config.php', 'w');
        fclose($handle);

        //now parse the config_si file and run installation.
        $singleActionLog .=  " run installer $nl";
        $inst = process_si($db, $inst, $inst_path);
        $filenames[] = $inst_path.'/install.log';
        $filenames[] = $inst_path.'/sugarcrm.log';

    }

    //change permissions back to 755 from 777
    $singleActionLog .=  " changing permissions back on directory, we are done. $nl";
    $perms_script = sprintf($perms = "chmod 755 '%s' -R ",$inst_path);
    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);

    //change User/Group ownership(if specified).
    if(isset($dce_config['client_cluster_group'])  && !empty($dce_config['client_cluster_group'])){
        $singleActionLog .=  " changing group $nl";
        $sprintStr .= $nl . shell_exec("chgrp -R ". $dce_config['client_cluster_group'] ." '".$inst_path."'");
    }
    if(isset($dce_config['client_cluster_user'])  && !empty($dce_config['client_cluster_user'])){
        $singleActionLog .=  " changing owner $nl";
        $sprintStr .= $nl . shell_exec("chown -R ".$dce_config['client_cluster_user'] ." '".$inst_path."'");
    }
    echo " from clonecore::  sprint_str = '$sprintStr'";

    $filenames['sprintStr'] =$sprintStr;
    $retArr ['filenames'] = $filenames;
    $retArr ['inst'] = $inst;
    echo " from clonecore::  return array is = "; print_r($retArr);
    return $retArr;

  }


  function deleteInstance($db, $action,$inst){
    global $dce_config, $singleActionLog;

    $singleActionLog .=  "processing delete action\n";
    $sprintStr = deleteCore($db, $action,$inst);

    $sprintStr = trim($sprintStr);
    if(empty($sprintStr)){
        // No errors, Connect to DCE DB and Update actions record to have status of ’Done’.
            $singleActionLog .=  " updating actions table\n";
            updateDCEAction($inst, $action['id'], $db, '');

        $singleActionLog .=  " done!!!!\n";
    }else{
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $sprintStr;
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors\n";
        return false;

    }
  }


  function deleteCore($db, $action,$inst,$reportErr = true){
    global $dce_config, $singleActionLog, $nl;
    $sprintStr ='';
    $dbPath = '';
    if ( empty($inst['name'])){
     $sprintStr .= 'Directory was not deleted because the passed in instance name is empty';
     return;
    }
    if(!empty($dce_config['client_mysql_path'])){$dbPath = checkSlash($dce_config['client_mysql_path']);}

    //grab right path
        $inst_path = checkSlash($dce_config['client_instancePath']) . $inst['name'];
        if($inst['status'] == 'archived'){
            $inst_path = checkSlash($dce_config['client_archivePath']) .$inst['name'];
        }
        $singleActionLog .=  "processing delete on path ". $inst_path;
    // check to make sure directory exists
    if (!file_exists(dirname($inst_path)) || !file_exists($inst_path)){
        $errString  = "Action with of type delete on instance".$inst['name']."could not be finished ...  ";
        if(!file_exists(dirname($inst_path))){
            $errString .= 'delete path'.dirname($inst_path).' is invalid';
        }else{
            $errString .= 'delete path'.($inst_path).' is invalid';
        }
        if($reportErr){
            reportError($action['id'], $inst, $db, $errString);
        }
        $singleActionLog .=  " done with errors $nl";
        return false;

    }

    //delete directory
    $singleActionLog .=  "removing path $nl";
    //using shell command because we need delete to be recursive
    $sprintStr .= $nl . shell_exec("rm -rf '".$inst_path."'");

    //remove user
    $singleActionLog .=  "removing user $nl";
    $removeUSR1 = "echo    \"delete from mysql.user where User='".$inst['db_user']."';\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($removeUSR1);
    $removeUSR2 = "echo    \"delete from mysql.db where User='".$inst['db_user']."';\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($removeUSR2);

    //remove db
    $singleActionLog .=  "removing db $nl";
    $removeDV = "echo    \"drop database ".$inst['db_user']." ;\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($removeDV);

    //flush the priviliges
    $singleActionLog .=  "flushing privileges $nl";
    $flush = "echo    \"flush privileges;\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($flush);

    return $sprintStr;
  }



  function archiveInstance($db, $action,$inst){
    global $dce_config, $singleActionLog, $nl;
    $sprintStr ='';
    $dbPath = '';

    if(!empty($dce_config['client_mysql_path'])){$dbPath = checkSlash($dce_config['client_mysql_path']);}
   //grab inst path
        $inst_path = checkSlash($dce_config['client_instancePath']) . $inst['name'];
    //grab archive path
        $arch_path = checkSlash($dce_config['client_archivePath']) . $inst['name'];
    $singleActionLog .=  "processing archive action on instance $inst_path";

    // check to make sure directories exist, and that archive does not already exist
    if (!file_exists($inst_path) || file_exists($arch_path) || !file_exists($dce_config['client_archivePath'])){
        $errString  = "Action of type archive on instance".$inst['name']."could not be finished ...  ";
        if(!file_exists($inst_path)){
            $errString .= ' instance path '.$inst_path.' is invalid '.$nl;
        }
        if(!file_exists($dce_config['client_archivePath'])){
            $errString .= ' archive path '.$dce_config['client_archivePath'].' is invalid'.$nl;
        }
        if(!file_exists($arch_path)){
            $errString .= ' archive directory already exists for instance '.dirname($inst_path) .$nl;
        }
        //  this is a big error, no need to go on
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;

    }

    //check to see if db has been dumped before
    if(file_exists("'$inst_path/dump.sql'")){
        //file has been dumped before, so rename
        rename("'$inst_path/dump.sql'",  "'".$inst_path.'/dump.sql'.TimeDate::getInstance()->nowDb()."'" );
    }

    //dump db
    $singleActionLog .=  "  dumping database $nl";
    $getDBDump = $dbPath."mysqldump -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer']."  ".$inst['db_user']." > ".$inst_path ."/dump.sql" ;
    $sprintStr .= $nl . shell_exec($getDBDump);


    //move files over
    $singleActionLog .=  "  moving folder to archive path $nl";
    //using shell command because we need move to be recursive
        $sprintStr .= $nl . shell_exec("mv '".$inst_path."'  '".$arch_path."'" );
        //rename("'".$inst_path."'",  "'".$arch_path."'" );

    //check that move went through
    if(!file_exists($arch_path)){
        //this is a big error, no need to go on
        $sprintStr .= "Folder was not moved succesfully from '$inst_path' to '$arch_path' $nl";
        reportError($action['id'], $inst, $db, $sprintStr);
        return false;
    }


    //remove user only if no errors have been returned
    $sprintStr = trim($sprintStr);
    if(empty($sprintStr) && file_exists($arch_path ."/dump.sql")){
        $singleActionLog .=  "  removing db user $nl";
        $removeUSR1 = "echo    \"delete from mysql.user where User='".$inst['db_user']."';\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
        $sprintStr .= $nl . shell_exec($removeUSR1);
        $removeUSR2 = "echo    \"delete from mysql.db where User='".$inst['db_user']."';\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
        $sprintStr .= $nl . shell_exec($removeUSR2);
    }else{
        $sprintStr .= $nl.' did not delete user from db because '.$nl;
        if(!empty($sprintStr)) $sprintStr .= $nl.'- shell exec returns are not empty.';
        if(!file_exists($arch_path ."/dump.sql")) $sprintStr .= $nl.' - sql dump file was not found in: '.$arch_path .'/dump.sql '.$nl;
    }

    //remove db only if no errors have been returned and dump succeeded
    $sprintStr = trim($sprintStr);
    if(empty($sprintStr) && file_exists($arch_path ."/dump.sql")){
        $singleActionLog .=  "  dropping db$nl";
        $removeDV = "echo    drop database ".$inst['db_user']." |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
        $sprintStr .= $nl . shell_exec($removeDV);
    }else{
        $sprintStr .= $nl.' did not delete user from db because: '.$nl;
        if(!empty($sprintStr)) $sprintStr .= $nl.' - shell exec returns are not empty.';
        if(!file_exists($arch_path ."/dump.sql")) $sprintStr .= $nl.' - sql dump file was not found in: '.$arch_path .'/dump.sql ';
    }

    //flush the priviliges
    $singleActionLog .=  "flushing privileges $nl";
    $flush = "echo    \"flush privileges;\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($flush);

    //change permissions back to 644 from 755
    $singleActionLog .=  "  changing permissions $nl";
    $perms_script = sprintf($perms = "chmod 644 '%s' -R ",$arch_path);
    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);

    $sprintStr = trim($sprintStr);
    if(empty($sprintStr)){
        // No errors, Connect to DCE DB and Update actions record to have status of ’Done’.
            $singleActionLog .=  " updating actions table. $nl";
            updateDCEAction($inst, $action['id'], $db, '');

        $singleActionLog .=  " done!!!! $nl";
    }else{
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $sprintStr;
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;

    }

  }


  function recoverInstance($db, $action, $inst){
    global $dce_config, $singleActionLog, $nl;
    $sprintStr ='';
    $dbPath = '';

    if(!empty($dce_config['client_mysql_path'])){$dbPath = checkSlash($dce_config['client_mysql_path']);}

   //grab inst path
    $inst_path = checkSlash($dce_config['client_instancePath']) . $inst['name'];

    //grab archive path
    $arch_path = checkSlash($dce_config['client_archivePath']) . $inst['name'];
    $singleActionLog .=  "  recovering files for  instance on path $arch_path $nl";

    // check to make sure directory exists
    if (!file_exists($arch_path) || file_exists($inst_path)){
        $errString  = "$nl Action with id: ".$action['id']." of type ".$action['type']." could not finish ...  $nl";
        if(!file_exists($arch_path)) $errString  .= "archived instance on path: ".$arch_path ."could not be found ...  $nl";
        if(file_exists($inst_path)) $errString  .= "instance on path: ".$inst_path ."already exists, recovery would overwrite the directory..$nl";
        $errString  .= " processing was stopped. $nl";
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;
    }


    //recover files
    //using shell command because we need move to be recursive
    $singleActionLog .=  "  moving folder to archive path $nl";
    $sprintStr .= $nl . shell_exec("mv '".$arch_path."'  '".$inst_path."'" );

    //make sure folder moved
    if(!file_exists($inst_path)){
        $errString  = "$nl Action with id: ".$action['id']." of type ".$action['type']." could not finish ...  ";
        $errString .= $nl.' the archived directory could not be moved back to the instance path, processing was halted.';
        $errString .= $sprintStr;
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " recovery action failed $nl";
        return false;
    }



    //change permissions on files
    $singleActionLog .=  "  changing permissions $nl";
    $perms_script = sprintf($perms = "chmod 755 '%s' -R ",$inst_path);
    //note we are not yet using php chmod because we need the call to be recursive.
    $sprintStr .= $nl . shell_exec($perms_script);


    //change User/Group ownership(if specified).
    if(isset($dce_config['client_cluster_group'])  && !empty($dce_config['client_cluster_group'])){
        $sprintStr .= $nl . shell_exec("chgrp -R ". $dce_config['client_cluster_group'] ." '".$inst_path."'");
    }
    if(isset($dce_config['client_cluster_user'])  && !empty($dce_config['client_cluster_user'])){
        $sprintStr .= $nl . shell_exec("chown -R ".$dce_config['client_cluster_user'] ." '".$inst_path."'");
    }


    //create new db
    $singleActionLog .=  "  creating db $nl";
    $newDB = "echo    \"create database ". $inst['db_user']." \" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($newDB);

    //create new db user
    $singleActionLog .=  "  creating db user $nl";
    $newUSR = "echo    \" grant all on ".$inst['db_user'].".* to '".$inst['db_user']."'@'localhost' identified by '".$inst['name']."'\" |".$dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer'];
    $sprintStr .= $nl . shell_exec($newUSR);

    //load db
    $singleActionLog .=  "  loading db $nl";
    $getDBDump = $dbPath."mysql -u ".$dce_config['client_dbUser']." --password=".$dce_config['client_dbPass']."   --host=".$dce_config['client_dbServer']."  ".$inst['db_user']." < ".$inst_path ."/dump.sql" ;
    $sprintStr .= $nl . shell_exec($getDBDump);

    //reset the admin password
    $randomPass = createRandPass(5);//site password
    $instDB = returnInstanceDB($inst['name']);
    if(empty($instDB)){
        // did not receive db, throw error
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString  .= "a database connection could not be made to instance  ".$inst['name'];
        $errString .= 'the following messages were returned while processing.. ';
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;
    }
    $usrUpdateQRY = "update users set user_hash = '".User::getPasswordHash($randomPass)."' where id = '1'";
    $instDB->query($usrUpdateQRY);

    $inst['site_pass'] = $randomPass;
    $inst['admin_pass'] = $randomPass;
    $instDB->close();

    $sprintStr = trim($sprintStr);
    if(empty($sprintStr)){
        // No errors, Connect to DCE DB and Update actions record to have status of ’Done’.
        $singleActionLog .=  " updating actions table. $nl";
        updateDCEAction($inst, $action['id'], $db, '');
        $singleActionLog .=  " done!!!! $nl";
    }else{
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $sprintStr;
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;
    }



  }


  function toggleUserOn($db, $action, $inst){
    global $dce_config, $singleActionLog, $nl;
    /*
    1. Script to enable/disable user, which will create/delete new admin user on the instance db is called.
    2. Connect to DCE DB and Update actions record to have status of ’Done’.
    */

    $singleActionLog .=  " processing toggle user on ".$inst['name'].$nl;
    $row = toggleUserOnCore($db,$action['modified_user_id'],$inst['name']);
    $cnt = $row['count'];

    if($cnt['num'] == 0){
        //insert did not happen, report the error
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors... $nl ";
        $errString .= 'the following query did not succeed ..'.$nl;
        $errString .= $row['qry'];
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;

    }

    //update actions table with user password param
    updateDCEAction($inst, $action['id'], $db, '', "usr_name:".$row['user_name'].",usr_pass:".$row['pass']);

  }


  function toggleUserOnCore($db, $modUserId, $instName){
    global $dce_config, $singleActionLog, $nl;

    //grab the user record from dce
    //create and execute query for User from Action
    $getUsrQry = "select id, user_name, first_name, last_name, is_admin, date_entered, date_modified, modified_user_id, created_by, title, status , deleted, employee_status from users ";
    $getUsrQry .= " where id = '$modUserId'";
    $singleActionLog .=  " Running query: $getUsrQry $nl";

    $usrQ = $db->query($getUsrQry);
    $Usr = $db->fetch_array($usrQ);
     $singleActionLog .=  "$nl User ----";
     $singleActionLog .=  var_export($Usr, false);
     $singleActionLog .=  $nl;

     //import the intance sugarconfig file and grab db info
     $singleActionLog .=  " grabbing sugarconfig file from instance $instName";
     $instDB = returnInstanceDB($instName);

    if(empty($instDB)){
        // did not receive db, throw error
        $errString  = "Action  finished with errors...  ";
        $errString  .= "a database connection could not be made to instance  ".$instName;
        $singleActionLog .= $errString;
        return $errString;
    }
    $Usr['id'] = $Usr['user_name'].'_SupportUser';
    $Usr['user_name'] = $Usr['user_name'].'_SupportUser';
    /*
    if ($Usr['id'] == '1' ||strpos($Usr['id'],'_id')) {
        $singleActionLog .=  "user id is from demo data, use another user_id";
        $Usr['id'] = $Usr['id'].'dce1234';
    }*/

//create the record to insert into db
    $pass = createRandPass();
    $user_hash = strtolower(md5($pass));
    $qry_insert  = "INSERT INTO users( id, user_hash, user_name, first_name, last_name, is_admin, date_entered, date_modified, modified_user_id, created_by, title, status , deleted, employee_status )";
    $qry_insert .= " VALUES ('".$Usr['id']."', ";
    $qry_insert .= " '$user_hash', ";
    $qry_insert .= " '".$Usr['user_name']."', ";
    $qry_insert .= " '".$Usr['last_name']."', ";
    $qry_insert .= " '".$Usr['first_name']."', ";
    $qry_insert .= " 1, '".gmdate('Y-m-d H:i:s')."', '".gmdate('Y-m-d H:i:s')."', '1', '1', 'DCE_CREATED', 'Active', 0, 'Active' )";



    //execute script
    $singleActionLog .=  "creating user in instance with following query:$qry_insert";
    $instDB->query($qry_insert);
    //make sure insert worked
    $select_qry = "select count(id) num from users where id = '".$Usr['id']."'";
    $loginRes = $instDB->query($select_qry);
    $row['count'] = $instDB->fetch_array($loginRes);
    $row['qry']=$qry_insert;
    $row['id']=$Usr['id'];
    $row['user_name']=$Usr['user_name'];
    $row['pass']=$pass;
    $instDB->close();

    return $row;

  }


  function toggleUserOff($db, $action, $inst){

    toggleUserOffCore($action['modified_user_id'],$inst['name'], $db);

    //update status
    updateDCEAction($inst, $action['id'], $db);

  }

  function toggleUserOffCore($modUserId, $instName, $db){
    global $dce_config, $singleActionLog, $nl;

    //grab the user record from dce
    //create and execute query for User from Action
    $getUsrQry = "select id, user_name, first_name, last_name, is_admin, date_entered, date_modified, modified_user_id, created_by, title, status , deleted, employee_status from users ";
    $getUsrQry .= " where id = '$modUserId'";
    $singleActionLog .=  " Running query: $getUsrQry $nl";

    $usrQ = $db->query($getUsrQry);
    $Usr = $db->fetch_array($usrQ);

    $userId = $Usr['user_name'].'_SupportUser';

    //create the record to insert into db
    $singleActionLog .=  " processing toggle user off ".$instName."with user $modUserId $nl";
    /*
    if ($modUserId == '1' || strpos($modUserId,'_id')){
         $modUserId = $modUserId.'dce1234';
    }
    */
    $qry_delete  = "delete from users where id = '$userId' and title = 'DCE_CREATED'";

    //import the intance sugarconfig file and grab db info
    $singleActionLog .=  " grabbing sugarconfig file from instance ". $instName;
    $instDB = returnInstanceDB($instName);

    if(empty($instDB)){
        // did not receive db, throw error
        $errString  = "Action  finished with errors...  ";
        $errString  .= "a database connection could not be made to instance  ".$instName;
        $singleActionLog .= $errString;
        return $errString;
    }

    //execute script
    $singleActionLog .=  "executing delete query: $qry_delete";
    $instDB->query($qry_delete);
    $instDB->close();

  }


  function gatherReportData($db, $action, $inst){
    global $dce_config, $singleActionLog;

    $singleActionLog .=  " gathering report data\n";
    //get list of all instances for this cluster
    $getParInstQry = "select name, id from dceinstances where dcecluster_id = '".$dce_config['client_cluster_id']."' and status='live' and deleted = 0 and from_copy_template = 0";
    $piqRes = $db->query($getParInstQry);
    while(($instRows[] = $db->fetch_array($piqRes))!= null)

    //gather info for each instance
    foreach($instRows as $row){
        if(empty($row['name'])) continue;

        //gather info for each instance Data is split into hours,
        //resulting in 24 Data Collection records inserted into dce per instance.
        $reportQueries = gatherInstanceData($row);

   //create db object to be used for inserting report queries
    $tempdb = new DB();

    //declare information for connection
    $tempdb->server = $dce_config['dce_dbServer'];
    $tempdb->user = $dce_config['dce_dbUser'];
    $tempdb->password = $dce_config['dce_dbPass'];
    $tempdb->database= $dce_config['dce_dbName'];

    //connect to DCE DB
    $tempdb->connect();

        //run each insert query (should be 24)
        foreach($reportQueries as $insertSQL){
            if (empty($insertSQL)){
                continue;
            }else{
                $tempdb->query($insertSQL);
            }
        }
        $tempdb->close();
    }

    //update Action
    updateDCEAction( null, $action['id'], $db, '', '', true);

  }


  function gatherInstanceData($inst){
        global $dce_config, $singleActionLog,$nl;

        //grab db connection to instance
        $instDB = returnInstanceDB($inst['name']);

        if(empty($instDB)){
            // did not receive db, throw error
            $errString  = "Action finished with errors...  ";
            $errString  .= "a database connection could not be made to instance  ".$inst['name'];
            $singleActionLog .= $errString;
            return false;
        }

        //get time ranges array
        $ranges = returnTimeRanges();
        $returnQueries = array();

        //gather info for instance by hour
        foreach($ranges as $range => $hour){
            //Num_of_logins - get all logins in given time range
            $loginsQry = "select distinct user_id from tracker_sessions  where date_start >= '". $hour['start']."' and date_start <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl numOfLogins query is: $loginsQry ";
            $loginRes = $instDB->query($loginsQry);
            //$numOfLogins = mysql_num_rows($loginRes);
            $numOfLogins = 0;
            while(($loginSum = $instDB->fetch_array($loginRes))!= null){
                if(empty($loginSum)) continue;
                $numOfLogins = $numOfLogins + 1;
            }

            //Num_of_users - get all active users, as well as inactive users that were modified in that time range
            $usersQry = "select id from users where status = 'Active' and deleted = 0 and date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."' ";
            $usersQry .= " Union select id from users where status = 'Inactive' and date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl numOfUsers query is: $usersQry ";
            $usersRes = $instDB->query($usersQry);
            //$numOfUsers = mysql_num_rows($usersRes);
            $numOfUsers = 0;
            while(($usersSum = $instDB->fetch_array($usersRes))!= null){
                if(empty($usersSum)) continue;
                $numOfUsers = $numOfUsers + 1;
            }


            //Max_num_sessions - # of sessions started in given time range
            $sessQry = "select id from tracker_sessions where date_start >= '". $hour['start']."' and date_start <= '".$hour['end'] ."'";

            $singleActionLog .= "$nl NumOfSessions query is: $sessQry ";
            $sessRes = $instDB->query($sessQry);
            //$numOfSess = mysql_num_rows($sessRes);
            $numOfSess = 0;
            while(($sesSum = $instDB->fetch_array($sessRes))!= null){
                if(empty($sesSum)) continue;
                $numOfSess = $numOfSess + 1;
            }

            //Num_of_requests - Sum of roundtrips per session
            $reqsQry = "select round_trips requests from tracker_sessions where date_start >= '". $hour['start']."' and date_start <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl numOfRequests query is:  $reqsQry ";
            $reqsRes = $instDB->query($reqsQry);
            $numOfreqs = 0;
            while(($reqSum = $instDB->fetch_array($reqsRes))!= null){
                if(empty($reqSum['requests'])) continue;
                $numOfreqs = $numOfreqs + $reqSum['requests'] ;
            }

            //Memory - Sum of memory Usage
            $memQry = "select memory_usage memory from tracker_perf where date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl memory query is: $memQry ";
            $memRes = $instDB->query($memQry);
            $memUsg = 0;
            while(($memSum = $instDB->fetch_array($memRes))!= null){
                if(empty($memSum['memory'])) continue;
                $memUsg = $memUsg + $memSum['memory'] ;
            }


            //Num_of_files
            $fileQry = "select files_opened files from tracker_perf where date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl file query is: $fileQry ";
            $fileRes = $instDB->query($fileQry);
            $fileUsg = 0;
            while(($fileSum = $instDB->fetch_array($fileRes))!= null){
                if(empty($fileSum['files'])) continue;
                $fileUsg = $fileUsg + $fileSum['files'] ;
            }


            //Num_of_queries - sum of db trips for given time range

            $dbQry = "select db_round_trips queries from tracker_perf where date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl Query Usage query is: $dbQry ";
            $dbRes = $instDB->query($dbQry);
            $dbUsg = 0;
            while(($dbSum = $instDB->fetch_array($dbRes))!= null){
                if(empty($dbSum['queries'])) continue;
                $dbUsg = $dbUsg + $dbSum['queries'] ;
            }


            //Last_login_time
            $lastLoginsQry = "select date_start from tracker_sessions where date_start >= '". $hour['start']."' and date_start <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl Last Log in query is: $lastLoginsQry ";
            $lastLoginRes = $instDB->query($lastLoginsQry);
            $lastLogin = null;
            while(($lastLoginRow = $instDB->fetch_array($lastLoginRes))!= null){
                if(empty($lastLoginRow))continue;
                $lastLogin = $lastLoginRow['date_start'];
            }

            //Slow_logged_queries compilation of all slow logging queries
            $sloLogQry = "select query_id, text from tracker_queries where date_modified >= '". $hour['start']."' and date_modified <= '".$hour['end'] ."'";
            $singleActionLog .= "$nl slo Log query is: $lastLoginsQry ";
            $sloLogRes = $instDB->query($sloLogQry);
            $sloLog = '';
            while(($sloLogRow = $instDB->fetch_array($sloLogRes))!= null){
                if(empty($sloLogRow))continue;
                $sloLog .= "$nl --------- ( Begin Query Id ".$sloLogRow['query_id'].") --------$nl";
                $sloLog .= $sloLogRow['text'];
                $sloLog .= "$nl --------- ( End Query Id ".$sloLogRow['query_id'].") --------$nl";
            }

            //Inst_name
            $in = $inst['name'];
            //Inst_id
            $ii = $inst['id'];
            //Time start
            $ts = $hour['start'];
            //Time end
            $te = $hour['end'];
            $g = createGuidOnDN();

            //use info gathered to create sql string
            $insertReportQRY  = 'INSERT INTO dcereports (id, name, date_entered, date_modified, modified_user_id , created_by , description, ';
            $insertReportQRY .= '  deleted ,  team_id , num_of_logins , num_of_users , max_num_sessions , num_of_requests ,';
            $insertReportQRY .= ' memory , num_of_files , num_of_queries , last_login_time , slow_logged_queries ,';
            $insertReportQRY .= ' instance_name , instance_id , time_start , time_end )';
            $insertReportQRY .= ' VALUES (';
            $insertReportQRY .= " '$g', '$in $range', '".gmdate('Y-m-d H:i:s')."', '".gmdate('Y-m-d H:i:s')."', '1', '1', NULL , ";
            $insertReportQRY .= "  '0', '1', '$numOfLogins', '$numOfUsers', '$numOfSess', '$numOfreqs', ";
            $insertReportQRY .= " '$memUsg', '$fileUsg', '$dbUsg', '$lastLogin', '$sloLog', ";
            $insertReportQRY .= " '$in', '$ii', '$ts', '$te' )";

            //put query into return array
            $returnQueries[] = $insertReportQRY;

            }

        //we are done gathering summary info for this instance, close db
        $instDB->close();

        //return array of queries representing 24 hours
        return $returnQueries;

  }

  function importInstances($db, $action, $inst){
    require_once('instanceImport.php');
    global $dce_config, $singleActionLog;
    $singleActionLog .=  " processing new instance\n";
    $rootpath =  checkSlash($dce_config['client_instancePath']) .$inst['name'];

    $importResult = importClientInstances($rootpath,$db);

    //update Action
    updateDCEAction( null, $action['id'], $db, '', '', true);

  }



  function upgradeInstance($db, $action, $inst,$exitOnConflicts = true, $deleteClone = true){
    global $dce_config, $singleActionLog, $nl;
    $sprintStr ='';
    $cleanupParms="parent_name:".$inst['name'].", ";

    //clone instance with db
    //create a copy of the instance to pass into clone_db;
    $cloneInst = $inst;
    $clone_action['clone_db'] = true;
    $prms = ',clone_db:true ';
    //create upgrade name for instance
    $cloneName = $inst['name'].'_'.createRandPass($numChars=4);
    $cloneInst['name'] = $cloneName;
    $cloneInst['url'] = dirname($inst['url']).$cloneName;
    $cloneInst['instance_path'] = dirname($inst['instance_path']).$cloneName;
    $retArr = cloneCore($db,$cloneInst, $inst, $prms);
    $filenames = $retArr['filenames'];
    $returned_clon_inst = $retArr['inst'];

    //if there were any errors, send out an error message
    if(!isset($filenames['sprintStr'])) $filenames['sprintStr'] = '';

    $filenames['sprintStr'] = trim($filenames['sprintStr']);
    if(!empty($filenames['sprintStr'])){
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." could not be completed... ";
        $errString .= 'the following messages were returned while processing.. ';
        $errString .= $filenames['sprintStr'];
        //remove files and database
        deleteCore($db, $action,$returned_clon_inst,false);
        reportError($action['id'], $returned_clon_inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;

    }

    $upGradeVars = getUpgradeVars($db, $action, $returned_clon_inst);

    if(empty($upGradeVars)){
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." could not be completed... ";
        $errString .= 'Upgrade variables could not be retrieved for this action ';
        //remove files and database
        deleteCore($db, $action,$returned_clon_inst,false);
        reportError($action['id'], $returned_clon_inst, $db, $errString);
        $singleActionLog .=  " done with errors $nl";
        return false;

    }
    if(isset($upGradeVars['delete_clone'])){
        $deleteClone = $upGradeVars['delete_clone'];
    }

    $skipDB = 'no';
    $exitOnConflict = 'yes';


    //perform upgrade dry run
    $upgradeSTR  ="silentUpgrade.php ";
    $upgradeSTR .=$upGradeVars['destTempUpgradePath']." ";
    $upgradeSTR .=$upGradeVars['logPath']." ";
    $upgradeSTR .=$upGradeVars['instPath']." ";
    $upgradeSTR .=$upGradeVars['srcTempPath']." ";
    $upgradeSTR .=$skipDB." " ;
    $upgradeSTR .=$exitOnConflict." ";
    $upgradeSTR .=checkSlash($dce_config['client_dir_path']).' ';

    //Execute upgrade call
    $sprintStr .= $nl . shell_exec(' php -f '. checkSlash($upGradeVars['destTempPath']).'modules/UpgradeWizard/'. $upgradeSTR);

    //change permissions and ownership
    $sprintStr .= $nl . changeFilePerms($upGradeVars['instPath'], true, 'chown');
    $sprintStr .= $nl . changeFilePerms($upGradeVars['instPath'], true, 'chmod');

    //call function to see if silentupgrade was succesful
    $dryRunStatus = checkHaystackFileForNeedle($upGradeVars['logPath'], "SilentUpgrade completed successfully");

    //if conflicts are found, then fail
    if(!$dryRunStatus){
        //dump clone record into a string, this will be used on dce side cleanup
        $firstTime  = true;
        $cleanupParms.=", clone:";
        foreach($returned_clon_inst as $col=>$val){
            if($firstTime){
                $cleanupParms.=$col.'='.$val;
                $firstTime  = false;
            }else{
                $cleanupParms.="| $col=$val";
            }
            $cleanupParms.=", ";
        }

        //now report failure.
        $errString = 'Upgrade action could not be completed on instance '. $inst['name'] .'Upgrade failed during dryrun. Check clone instance named '.$cloneName;
        $filenames[] = $upGradeVars['logPath'];
        //pass in instance query to run, as well as path to log file
        reportError($action['id'], $inst, $db, $errString, $filenames,$cleanupParms);
        return false;
    }
        //no conflicts were found we can delete clone now if this is to go live
        if($deleteClone){
            deleteCore($db, $action,$returned_clon_inst,false);
        }else{
            //dump clone record into a string, this will be used on dce side cleanup

            $firstTime  = true;
            $cleanupParms.="clone:";
            foreach($returned_clon_inst as $col=>$val){
                if($firstTime){
                    $cleanupParms.=$col.'='.$val;
                    $firstTime  = false;
                }else{
                    $cleanupParms.="| $col=$val";
                }
            }
            $cleanupParms.=",";
        }

    if($action['type'] == 'upgrade_test'){
        //this is a test run, so pass in clone info and wrap up
           $filenames[] = $upGradeVars['instPath'].'/upgradeWizard.log';
           updateDCEAction($returned_clon_inst, $action['id'], $db, $filenames,$cleanupParms);
           return true;
    }

    //  no conflicts found and this is live run, so perform upgrade again on LIVE instance.
    if($action['type'] == 'upgrade_live'){
        //put up 'site closed' index page.
        $busyIndexPath = $dce_config['upgradeBusyPage'];
        $indexPath = checkSlash($dce_config['client_instancePath']) . $inst['name'].'/index.php';
        $tempIndexPath = checkSlash($dce_config['client_instancePath']) . $inst['name'].'/tmpIndex.php';
        //first copy the original index to a temp file
        copy($indexPath, $tempIndexPath);
        //now copy busy page over to replace index
        copy($busyIndexPath, $indexPath);

        //run upgrade
        $skipDB = 'no';
        $exitOnConflict = 'no';
        $instPath = checkSlash($dce_config['client_instancePath']) . $inst['name'];
        $logPath = checkslash($instPath).'silentupgrade_live.log';


        //change permissions and ownership
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chown');
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chmod');

        //perform upgrade live run
        $upgradeSTR  ="silentUpgrade.php ";
        $upgradeSTR .=$upGradeVars['destTempUpgradePath']." ";
        $upgradeSTR .=$logPath." ";
        $upgradeSTR .=$instPath." ";
        $upgradeSTR .=$upGradeVars['srcTempPath']." ";
        $upgradeSTR .=$skipDB." " ;
        $upgradeSTR .=$exitOnConflict." ";
        $upgradeSTR .=checkSlash($dce_config['client_dir_path']).' ';
        $sprintStr .= $nl . shell_exec($upgradeSTR);
        $sprintStr .= $nl . shell_exec(' php -f '. checkSlash($upGradeVars['destTempPath']).'modules/UpgradeWizard/'. $upgradeSTR);


        //change permissions and ownership
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chown');
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chmod');

        //call function to see if silentupgrade was succesful
        //$upgradeStatus = checkHaystackFileForNeedle(checkSlash($dce_config['client_instancePath']) . $inst['name'], "SUCCESS");
        $upgradeStatus = checkHaystackFileForNeedle($logPath, "SilentUpgrade completed successfully");

        //if conflicts are found, then fail
        if(!$upgradeStatus){

            //report failure.
            $errString = 'Upgrade action could not be completed on instance '. $inst['name'] .'Upgrade failed after live run. ';
            $filenames[] = $logPath;
            //pass in instance query to run, as well as path to log file
            //note that we are continuing upgrade sine we want the 'site down' page removed
            reportError($action['id'], $inst, $db, $errString, $filenames,$cleanupParms);
            return false;
        }

        //remove site closed message and place index back
        unlink($indexPath);
        rename($tempIndexPath, $indexPath);


        //lets change ini_setup.php to point to new directory
        $singleActionLog .=  " rewriting ini_setup.php and .htaccess to point to new directory";
        file_replace($upGradeVars['srcTempPath'], $upGradeVars['destTempPath'], $instPath .'/ini_setup.php');
        file_replace($upGradeVars['srcTempURL'], $upGradeVars['destTempURL'], $instPath .'/ini_setup.php');
        file_replace($upGradeVars['srcTempURL'], $upGradeVars['destTempURL'], $instPath .'/.htaccess');

        //change permissions and ownership
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chown');
        $sprintStr .= $nl . changeFilePerms($instPath, true, 'chmod');

        // Connect to DCE DB and Update actions record to have status of ’Done’ if upgrade succeeded


           $filenames[] = $upGradeVars['instPath'].'/upgradeWizard.log';
           updateDCEAction($inst, $action['id'], $db, $filenames,$cleanupParms);
    }


    $singleActionLog .=  " done!!!! $nl";

  }

  function updateKey($db, $action, $inst){
    global $singleActionLog, $nl;
    //update the license values in install config table

    $instDB = returnInstanceDB($inst['name']);
    if(empty($instDB)){
        // did not receive db, throw error
        $errString  = "Action with id: ".$action['id']." of type ".$action['type']." finished with errors...  ";
        $errString  .= "a database connection could not be made to instance  ".$inst['name'];
        $errString .= 'the following messages were returned while processing.. ';
        reportError($action['id'], $inst, $db, $errString);
        $singleActionLog .=  " Could not update the license info for instance ".$inst['name']." .done with errors $nl";
        return false;
    }
    $singleActionLog .=  "$nl about to update license info for db $instDB->database $nl";
    //create query using values from instance
    if(isset($inst['license_oc']) && !empty($inst['license_oc'])){
        $cnfgUpdateQRYArr['num_lic_oc'] = "update config set '{$inst['license_oc']}' where name='num_lic_oc' and category='license'";
    }
    if(isset($inst['licensed_users']) && !empty($inst['licensed_users'])){
        $cnfgUpdateQRYArr['users'] = "update config set value ='{$inst['licensed_users']}' where name='users' and category='license'";
    }
    if(isset($inst['license_expire_date']) && !empty($inst['license_expire_date'])){
        $cnfgUpdateQRYArr['expire_date'] = "update config set value ='{$inst['license_expire_date']}' where name='expire_date' and category='license'";
    }
    if(isset($inst['license_key']) && !empty($inst['license_key'])){
        $cnfgUpdateQRYArr['key'] = "update config set value ='{$inst['license_key']}' where name='key' and category='license'";
    }

    foreach ($cnfgUpdateQRYArr as $cnfgUpdateQRY){
        if (empty($cnfgUpdateQRYArr)) continue;
        $singleActionLog .=  "$nl running query: $cnfgUpdateQRY $nl";
        $instDB->query($cnfgUpdateQRY);
    }
    $singleActionLog .=  " closing instance db $nl";
    $instDB->close();

  //we are done, update success
  $singleActionLog .=  " update license info action is complete $nl";
    updateDCEAction( null, $action['id'], $db, '', '', true);

 }
?>

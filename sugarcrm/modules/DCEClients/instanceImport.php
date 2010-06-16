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
 
 
    function importClientInstances($rootpath,$db){
        require_once('db.php');
        require_once('client_utils.php');
        require_once('dce_config.php'); 
        global $singleActionLog;
    
        //takes directory of instances as input
        // process new instance to be created
         $singleActionLog .=  "<br> Begin Import Process \n";
        $rootpath = checkSlash($rootpath);
    
        //make sure that the instance path exists and is a directory
        if(empty($rootpath) || !file_exists($rootpath)){
         //Root Directory is invalid
         $singleActionLog ='Root Directory Input does not exist';
         echo $singleActionLog ;
         return false;
        }
        
        if(!is_dir("$rootpath")){
         //Root Directory is invalid
         $singleActionLog  .= 'input is not a directory';
         echo $singleActionLog ;
         return true;
        }    
       
        //make sure dummy template exists
        $templResults = createCopyTemplate($db);
        $instanceArr = array();
        $handle = opendir("$rootpath");
        $singleActionLog .=  "<br> processing root directory ..$dir\n";
        //loop through the root directory for each subdirectory (Instance root directory)
        while (false !== ($dir = readdir($handle))) {
            //make sure you go into directory tree and not out of tree
            if($dir!= '.' && $dir!= '..'){
                //retrieve file for this directory
                $fileArr = scandir($rootpath.$dir);
            
                //if ini_setup.php is not found, then validate this is an instance
                if(!in_array('ini_setup.php',$fileArr)){
                
                    //looks for index.php and config.php
                    if(in_array('index.php',$fileArr) && in_array('config.php',$fileArr)){    
                        //add details to instance array for later processing
                        //$instanceArr[$dir] = $rootpath.$dir;
                        $singleActionLog .=  "<br> creating instance for .... $dir\n";
                        $singleActionLog  .= createCopyTemplateInstanceRecord($dir,$rootpath,$db).'\n';
                        
                    }
                        
                }            
                            
            }  
          }
          $singleActionLog .=  "<br> Import Process has finished\n";
        return true;
    }    
    
    function createCopyTemplateInstanceRecord($name, $rootpath, $db){
        global $dce_config;
        require_once('client_utils.php');
        if(empty($name)) return false;
    
        //first check to see this instance name does not already exist
        $chkInstQry = "select count(id) num from dceinstances where dcecluster_id = '".$dce_config['client_cluster_id']."' and name ='$name'  and deleted = 0";
        $rez = $db->query($chkInstQry);
        $row = $db->fetch_array($rez);

    
        if($row['num'] != 0){
            //return error, the name already exists
            echo 'could not create instance based on directory name '.$name.', instance with that name already exists';
            return 'could not create instance based on directory name '.$name.', instance with that name already exists';   
        }
        $id = createGuidOnDN();
        $date = gmdate("Y-m-d H:i:s");
                            
        //creates query that will insert instance record
        $newInstQry = 
            "INSERT INTO dceinstances (id , name , date_entered , date_modified , modified_user_id , created_by ,
            description , deleted , team_id , assigned_user_id , account_id , license_key , type , licensed_users ,
            license_start_date , license_duration , admin_user , admin_pass , internal_record , license_oc ,
            demo_data , dcetemplate_id , dcecluster_id , si_config_file , status , url , instance_path , support_user ,
            last_accessed , db_user , db_pass , parent_dceinstance_id , get_key_user_id , update_key_user_id ,
            from_copy_template)
            VALUES ('$id', '$name', '$date', '$date', '1', '1', 
            NULL , '0', '1', '1', NULL , '$id', 'production', '5', 
            '$date', '365', NULL , NULL , NULL , NULL , 
            '0', 'ypt0d368-c708-c7c8-1682-47d57b16copy', '".$dce_config['client_cluster_id']."' , NULL , 'live', '".$dce_config['client_baseURL'].$name."', '$rootpath$name', '0', 
            NULL , NULL , NULL , NULL , '', NULL , '1')";    

        $db->query($newInstQry);
    
    
    }
 
 
    function createCopyTemplate($db){
        //check to see if template exists.
        $chkTemplQRY = "select count(id) num from dcetemplates where id = 'ypt0d368-c708-c7c8-1682-47d57b16copy'";
            $rez = $db->query($chkTemplQRY);
            $row = $db->fetch_array($rez);
        
            if($row['num'] == 1){
                //template already exists, no need to proceed
                return true;   
            }
             
        //if template does not exist, create it.
        $newTmplQry = 
            "INSERT INTO dcetemplates (id, name, date_entered, date_modified, 
            modified_user_id, created_by, description, deleted, team_id, assigned_user_id, status, 
            sugar_version, sugar_edition, upgrade_acceptable_edition, upgrade_acceptable_version, template_name, 
            zip_name, convert_status, copy_template) VALUES
            ('ypt0d368-c708-c7c8-1682-47d57b16copy', 'Sugar-CopyTemplate', '2008-05-01 23:20:22', '2008-05-01 23:20:22',
             '1', '1', NULL, 0, '1', '1', 'active', 
            '0.0.0', 'CPY', NULL, NULL, 'Sugar-CopyTemplate', 
            'none.zip', 'yes', 1)";

        $db->query($newTmplQry);
           
        
    } 
     
 
 
 
 
 
 
 
?>

<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
 
 require_once('include/pclzip/pclzip.lib.php');
 
 $templ = new DCETemplate();
 $templ->retrieve($_REQUEST['record']);
 
 //retrieve template directory
 $adm = new Administration();
 $adm->retrieveSettings();
 $temp_dir =$adm->settings['dce_templates_dir'];
 $temp_dir = str_replace('\\','/',$temp_dir);
 if(substr($temp_dir, -1, 1) != '/') $temp_dir .= '/';
    
 //set template and template zip paths
 $templ_zip_path= $temp_dir . $templ->zip_name;
 $templ_path= $temp_dir . $templ->template_name;
                        
 //make sure template zip exists
  if(!file_exists($templ_zip_path) || !is_file($templ_zip_path)){
    $GLOBALS['log']->fatal("DCETemplates::convertTemplate..  template does not exist in path ".$templ_zip_path);
    $templ->convert_status = 'error';
     $templ->save();
     return false;    
  }
  
  //unzip the template
    $archive = new PclZip($templ_zip_path);
  if ($archive->extract(PCLZIP_OPT_PATH, $temp_dir, PCLZIP_OPT_SET_CHMOD, 0777) == 0) {
    $GLOBALS['log']->fatal("DCETemplates::convertTemplate..  could not extract the template");     
    $templ->convert_status = 'error';
     $templ->save();
     return false;
  }

 //make sure template zip exists
  if(!file_exists($templ_path)){
    $GLOBALS['log']->fatal("DCETemplates::convertTemplate..  template passed unzipping process, but could not be retrieved as ".$templ_path);
     $templ->convert_status = 'error';
     $templ->save();
     return false;
  }

 //set the template path
 $_GET['TEMPLATE_PATH'] = $templ_path ;
 $_REQUEST['TEMPLATE_PATH'] = $templ_path ;
 
 $success = false;
 
 //import the template Converter file
 require_once('modules/DCEClients/templateConverter.php');
 
 //check to see that the template was converted succesfully.
 if($success){

    //set the template status to converted
    $templ->convert_status = 'yes';     
    $templ->save();

    //perform cleanup work    
     $zipName = $templ->zip_name;
     $zipName = substr($zipName, 0, -4);
     $converted = new PclZip($temp_dir.$zipName.'_conv.zip');
     $v_list = $converted->create($templ_path, PCLZIP_OPT_REMOVE_PATH, $temp_dir);


  if ($v_list == 0) {
    $GLOBALS['log']->fatal("DCETemplates::convertTemplate..  rezipping did not work for file ". $temp_dir.$zipName.'_conv.zip');
        $templ->convert_status = 'error';
         $templ->save();
         return false;        
    
  }
 }else{
    //set the template status to converted
    $templ->convert_status = 'error';     
    $templ->save();
 }
     
?>

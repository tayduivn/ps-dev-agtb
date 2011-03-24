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

require_once('include/JSON.php');
require_once('include/entryPoint.php');

global $sugar_config;

$json = getJSONobj();
$rmdir=true;
$returnArray = array();
if($json->decode(html_entity_decode($_REQUEST['forQuotes']))){
    $returnArray['forQuotes']="quotes";
}else{
    $returnArray['forQuotes']="company";
}
if(isset($_FILES['file_1'])){
    $uploadTmpDir=$sugar_config['tmp_dir'].'tmp_logo_'.$returnArray['forQuotes'].'_upload';
    $file_name = $uploadTmpDir . DIRECTORY_SEPARATOR .  cleanFileName(basename($_FILES['file_1']['name']));
    if(file_exists($uploadTmpDir))
       rmdir_recursive($uploadTmpDir);
    
    mkdir_recursive( $uploadTmpDir,null,true );
    if (!empty($_FILES['file_1']['error'])){
        rmdir_recursive($uploadTmpDir);
        $returnArray['data']='not_recognize';
        echo $json->encode($returnArray);
        sugar_cleanup();
        exit();
    }
    if (!move_uploaded_file($_FILES['file_1']['tmp_name'], $file_name)){
        rmdir_recursive($uploadTmpDir);
        die("Possible file upload attack!\n");
    }
}else{
    $returnArray['data']='not_recognize';
    echo $json->encode($returnArray);
    sugar_cleanup();
    exit();
}
if(file_exists($file_name) && is_file($file_name)){
    $returnArray['path']=$file_name;
    $img_size = getimagesize($file_name);
    $filetype = $img_size['mime'];
    if(($filetype != 'image/jpeg' && $filetype != 'image/png') ||  ($filetype != 'image/jpeg' && $returnArray['forQuotes'] == 'quotes')){
        $returnArray['data']='other';
    }else{
        $test=$img_size[0]/$img_size[1];
        if (($test>10 || $test<1) && $returnArray['forQuotes'] == 'company'){
            $rmdir=false;
            $returnArray['data']='size';
        }
        if (($test>20 || $test<3)&& $returnArray['forQuotes'] == 'quotes')
            $returnArray['data']='size';
    }
    if(!empty($returnArray['data'])){
        echo $json->encode($returnArray);
    }else{
        $rmdir=false;
        $returnArray['data']='ok';
        echo $json->encode($returnArray);
    }
}else{
    $returnArray['data']='file_error';
    echo $json->encode($returnArray);
}
if($rmdir)
    rmdir_recursive($uploadTmpDir);
sugar_cleanup();
exit();
?>

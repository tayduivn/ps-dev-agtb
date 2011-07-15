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

require_once('include/pclzip/pclzip.lib.php');
global $db;
$rmdir=true;
if(isset($_FILES['file_1'])){
    $uploadTmpDir=$GLOBALS['sugar_config']['upload_dir'].'tmp_'.time();
    $file_name = $uploadTmpDir .'/'. basename($_FILES['file_1']['name']);
    mkdir( $uploadTmpDir );
    if (!empty($_FILES['file_1']['error'])){
        echo 'not_recognize';
        sugar_cleanup();
        exit();
    }
    if (!move_uploaded_file($_FILES['file_1']['tmp_name'], $file_name)){
        die("Possible file upload attack!\n");
    }
}
else{
	echo 'not_recognize';
	sugar_cleanup();
	exit();
}
if(file_exists($file_name) && is_file($file_name)){
    $fileExp = explode('.', $file_name);
    $filetype = $fileExp[count($fileExp) -1]; //file extension will be last index in array, -1 for 0-based indexes
    if($filetype != 'zip'){
        echo 'other';
    }else{
        if(($filesize=filesize($file_name)) != null){
            if(($filesize > return_bytes(ini_get("upload_max_filesize"))) || ($filesize > return_bytes(ini_get("post_max_size")))){
                echo 'size';
                //$response= "<script>alert('File size is bigger than the max_upload-size setting in php.ini. Upgrade attempt will fail. Increase the upload_max_size in php.ini to greater than ')</script>";
            }else{
                $archive = new PclZip( "$file_name" );
                $list=$archive->listContent();
                $return = false;
                foreach($list as $file){
                    if(preg_match('/.*\/DCEUpgrade\/.*\/manifest.php$/',$file['filename'])){
                        $archive->extractByIndex($file['index'], $uploadTmpDir, str_replace("manifest.php","",$file['filename']));
                        include("$uploadTmpDir/manifest.php");
                        if($manifest['type']=='patch' && $manifest['acceptable_sugar_versions']['regex_matches']){
                            $manifest['acceptable_sugar_versions']['regex_matches'] = str_replace(array("/","\\"),"",$manifest['acceptable_sugar_versions']['regex_matches']);
                            foreach($manifest['acceptable_sugar_versions']['regex_matches'] as $v){
                                $manifestArr['patch'][]=$v;
                            }
                        }elseif($manifest['type']=='flavor' && $manifest['acceptable_sugar_flavors']){
                            foreach($manifest['acceptable_sugar_flavors'] as $v){
                                $manifestArr['flavor'][]=$v;
                            }
                        }

                    }if(preg_match('/.*\/sugar_version.php$/',$file['filename'])){
                        $archive->extractByIndex($file['index'], $uploadTmpDir, str_replace("sugar_version.php","",$file['filename']));
                        include("$uploadTmpDir/sugar_version.php");
                        $zip_root_folder=str_replace(array("/","\\"),"",$list[0]['filename']);

                        $query = "SELECT template_name FROM dcetemplates where deleted = 0 AND template_name='$zip_root_folder'";
                        $rows = array();
                        $result = $db->query($query);
                        while (($row = $db->fetchByAssoc($result)) != null) {
                           $rows[]=$row;
                        }
                        if(!empty($rows))
                            $return='duplicate';
                        else
                            $return = true;
                    }

                }
                if($return === true){
                    $upgradeEdition = '';
                    $upgradeVersion = '';
                    if(isset($manifestArr['patch'])){
                        $manifestArr['patch'] = array_unique($manifestArr['patch']);
                        $upgradeVersion=implode(" | ", $manifestArr['patch']);
                    }
                    if(isset($manifestArr['flavor'])){
                        $manifestArr['flavor'] = array_unique($manifestArr['flavor']);
                        $upgradeEdition=implode(" | ", $manifestArr['flavor']);
                    }
                    $rmdir=false;
                    echo "SUGARDCE^,^$sugar_flavor^,^$sugar_version^,^$zip_root_folder^,^$uploadTmpDir^,^{$_FILES['file_1']['name']}^,^UPGRADE_EDITION^,^{$upgradeEdition}^,^UPGRADE_VERSION^,^$upgradeVersion";
                }else if($return === 'duplicate'){
                    echo 'duplicate';
                }else{
                    echo 'error';
                }
            }
        }
    }
}else{
    echo 'none';
}
if($rmdir){
    rmdir_recursive($uploadTmpDir);
}
sugar_cleanup();
exit();
?>

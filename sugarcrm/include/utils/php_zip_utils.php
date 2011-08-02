<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: zip_utils.php 16276 2006-08-22 18:56:15Z awu $
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

function unzip( $zip_archive, $zip_dir)
{
   return unzip_file($zip_archive, null, $zip_dir);
}

function unzip_file( $zip_archive, $archive_file, $zip_dir)
{
    if( !is_dir( $zip_dir ) ){
        if (!defined('SUGAR_PHPUNIT_RUNNER'))
            die( "Specified directory '$zip_dir' for zip file '$zip_archive' extraction does not exist." );
        return false;
    }
    $zip = new ZipArchive;
    $res = $zip->open($zip_archive);
    if($res !== true) {
        if (!defined('SUGAR_PHPUNIT_RUNNER'))
            die(sprintf("ZIP Error(%d): %s", $res, $zip->status));
        return false;
    }

    if($archive_file !== null) {
        $res = $zip->extractTo($zip_dir, $archive_file);
    } else {
        $res = $zip->extractTo($zip_dir);
    }
    if($res !== true) {
        if (!defined('SUGAR_PHPUNIT_RUNNER'))
            die(sprintf("ZIP Error(%d): %s", $res, $zip->status));
        return false;
    }
    return true;
}

function zip_dir( $zip_dir, $zip_archive )
{
    if( !is_dir( $zip_dir ) ){
        if (!defined('SUGAR_PHPUNIT_RUNNER'))
            die( "Specified directory '$zip_dir' for zip file '$zip_archive' extraction does not exist." );
        return false;
    }
    $zip = new ZipArchive();
    $zip->open($zip_archive, ZIPARCHIVE::CREATE|ZIPARCHIVE::OVERWRITE);
    $path = realpath($zip_dir);
    $chop = strlen($path)+1;
    $dir = new RecursiveDirectoryIterator($path);
    $it = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $k => $fileinfo) {
        // Bug # 45143
        // ensure that . and .. are not zipped up, otherwise, the
        // CENT OS and others will fail when deploying module
        $fileName = $fileinfo->getFilename();
        if ($fileName == "." || $fileName == "..")
            continue; 
        $localname = substr($fileinfo->getPathname(), $chop);
        if($fileinfo->isDir()) {
            $zip->addEmptyDir($localname);
        } else {
            $zip->addFile($fileinfo->getPathname(), $localname);
        }
    }
}

/**
 * Zip list of files, optionally stripping prefix
 * @param string $zip_file
 * @param array $file_list
 * @param string $prefix Regular expression for the prefix to strip
 */
function zip_files_list($zip_file, $file_list, $prefix = '')
{
    $archive    = new ZipArchive();
    $res = $archive->open($zip_file, ZipArchive::CREATE|ZipArchive::OVERWRITE);
    if($res !== TRUE)
    {
        $GLOBALS['log']->fatal("Unable to open zip file, check directory permissions: $zip_file");
        return FALSE;
    }
    foreach($file_list as $file) {
        if(!empty($prefix) && preg_match($prefix, $file, $matches) > 0) {
            $zipname = substr($file, strlen($matches[0]));
        } else {
            $zipname = $file;
        }
        $archive->addFile($file, $zipname);
    }
    return TRUE;
}

<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2007 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: ImportCacheFiles.php 31561 2008-02-04 18:41:10Z jmertic $
 * Description: Static class to that is used to get the filenames for the various
 * cache files used
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
 
class ImportCacheFiles
{
    /**
     * Returns the filename for a temporary file
     *
     * @param  string $type string to prepend to the filename, typically to indicate the file's use
     * @return string filename
     */
    private static function _createFileName(
        $type = 'misc'
        )
    {
        global $sugar_config, $current_user;
        
        if( !is_dir($sugar_config['import_dir']) )
            create_cache_directory(preg_replace('/^cache\//','',$sugar_config['import_dir']));
        
        if( !is_writable($sugar_config['import_dir']) )
            return false;
        
        return "{$sugar_config['import_dir']}{$type}_{$current_user->id}.csv";        
    }
    
    /**
     * Returns the duplicates filename
     *
     * @return string filename
     */
    public static function getDuplicateFileName()
    {
        return self::_createFileName("dupes");
    }
    
    /**
     * Returns the error filename
     *
     * @return string filename
     */
    public static function getErrorFileName()
    {
        return self::_createFileName("error");
    }
    
    /**
     * Returns the error records filename
     *
     * @return string filename
     */
    public static function getErrorRecordsFileName()
    {
        return self::_createFileName("errorrecords");
    }
    
    /**
     * Returns the status filename
     *
     * @return string filename
     */
    public static function getStatusFileName()
    {
        return self::_createFileName("status");
    }
    
    /**
     * Clears out all cache files in the $sugar_config['import_dir'] directory
     */
    public static function clearCacheFiles()
    {
        global $sugar_config;
        
        if ( is_dir($sugar_config['import_dir']) ) {
            $files = dir($sugar_config['import_dir']);
            while (false !== ($file = $files->read())) {
                if ( !is_dir($file) && stristr($file,'.csv') )
                    unlink($sugar_config['import_dir'].$file);
            }
        }
    }
}

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
 
require_once 'modules/Import/ImportCacheFiles.php';

class SugarTestImportUtilities
{
    public static  $_createdFiles = array();

    private function __construct() {}

    public function __destruct()
    {
        self::removeAllCreatedFiles();
    }

    public static function createFile($lines = 2000,$columns = 3)
    {
        $filename = $GLOBALS['sugar_config']['import_dir'].'test'. uniqid();
        $fp = fopen($filename,"w");
        for ($i = 0; $i < $lines; $i++) {
            $line = array();
            for ($j = 0; $j < $columns; $j++)
                $line[] = "foo{$i}{$j}";
            fputcsv($fp,$line);
        }
        fclose($fp);
        
        self::$_createdFiles[] = $filename;
        
        return $filename;
    }
	
    public static function createFileWithEOL(
        $lines = 2000,
        $columns = 3
        ) 
    {
        $filename = $GLOBALS['sugar_config']['import_dir'].'test'.date("YmdHis");
        $fp = fopen($filename,"w");
        for ($i = 0; $i < $lines; $i++) {
            $line = array();
            for ($j = 0; $j < $columns; $j++) {
            	// test both end of lines: \r\n (windows) and \n (unix)
                $line[] = "start{$i}\r\n{$j}\nend";
            }
            fputcsv($fp,$line);
        }
        fclose($fp);
        
        self::$_createdFiles[] = $filename;
        
        return $filename;
    }
	
    public static function createFileWithWhiteSpace() 
    {
        $filename = $GLOBALS['sugar_config']['import_dir'].'testWhiteSpace'.date("YmdHis");
        $contents = <<<EOTEXT
account2,foo bar
EOTEXT;
        file_put_contents($filename, $contents);
        
        self::$_createdFiles[] = $filename;
        
        return $filename;
    }
    
    public static function removeAllCreatedFiles()
    {
        foreach ( self::$_createdFiles as $file ) {
            @unlink($file);
            $i = 0;
            while(true) {
                if ( is_file($file.'-'.$i) ) 
                    unlink($file.'-'.$i++);
                else 
                    break;
            }
        }
        ImportCacheFiles::clearCacheFiles();
    }
}

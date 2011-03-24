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

// $Id: PearDatabase.php 39146 2008-08-27 00:16:04Z awu $

require_once('include/database/DBManager.php');

/**
 * @deprecated
 */
class PearDatabase
{
    /**
     * Returns DBManager instance
     *
     * @deprecated
     * @param  string $instanceName optional, name of the instance
     * @return object DBManager instance 
     */
    public static function getInstance($instanceName='')
    {
        $GLOBALS['log']->info('call to PearDatabase::getInstance() is deprecated');
        return DBManagerFactory::getInstance($instanceName);
    }
    
    /**
     * Returns a quoted string
     *
     * @deprecated
     * @param  string $string
     * @param  bool   $isLike optional
     * @return string
     */
    public static function quote(
        $string,
        $isLike = true
        )
    {
        $GLOBALS['log']->info('call to PearDatabase::quote() is deprecated');
        return $GLOBALS['db']->quote($string, $isLike);
    }

    /**
     * Quotes each string in the given array
     *
     * @deprecated
     * @param  array  $array
     * @param  bool   $isLike optional
     * @return string
     */
    public static function arrayQuote(
        array &$array, 
        $isLike = true
        ) 
    {
        $GLOBALS['log']->info('call to PearDatabase::arrayQuote() is deprecated');
        return $GLOBALS['db']->arrayQuote($array, $isLike);
    }
    
    /**
     * Truncates a string to a given length
     *
     * @deprecated
     * @param string $string
     * @param int    $len    length to trim to
     * @param string
     */
    public static function truncate(
        $string, 
        $len
        ) 
    {
        $GLOBALS['log']->info('call to PearDatabase::truncate() is deprecated');
        if ( is_numeric($len) && $len > 0 )
                $string=mb_substr($string,0,(int) $len);
        return $string;
    }


}
?>

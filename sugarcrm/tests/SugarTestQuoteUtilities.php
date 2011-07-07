<?php
//FILE SUGARCRM flav=pro ONLY
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
 
$beanList = array();
$beanFiles = array();
require('include/modules.php');
$GLOBALS['beanList'] = $beanList;
$GLOBALS['beanFiles'] = $beanFiles;
require_once 'modules/Quotes/Quote.php';

class SugarTestQuoteUtilities
{
    private static $_createdQuotes = array();

    private function __construct() {}

    public static function createQuote($id = '') 
    {
        $time = mt_rand();
    	$name = 'SugarQuote';
    	$quote = new Quote();
        $quote->name = $name . $time;
        $quote->quote_stage = 'Draft';
        $quote->date_quote_expected_closed = $GLOBALS['timedate']->to_display_date(gmdate('Y-m-d'));
        if(!empty($id))
        {
            $quote->new_with_id = true;
            $quote->id = $id;
        }
        $quote->save();
        self::$_createdQuotes[] = $quote;
        return $quote;
    }

    public static function setCreatedQuote($quote_ids) {
    	foreach($quote_ids as $quote_id) {
    		$quote = new Quote();
    		$quote->id = $quote_id;
        	self::$_createdQuotes[] = $quote;
    	} // foreach
    } // fn
    
    public static function removeAllCreatedQuotes() 
    {
        $quote_ids = self::getCreatedQuoteIds();
        $GLOBALS['db']->query('DELETE FROM quotes WHERE id IN (\'' . implode("', '", $quote_ids) . '\')');
    }
        
    public static function getCreatedQuoteIds() 
    {
        $quote_ids = array();
        foreach (self::$_createdQuotes as $quote) {
            $quote_ids[] = $quote->id;
        }
        return $quote_ids;
    }
}
?>
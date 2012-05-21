<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'modules/OpportunityLines/OpportunityLine.php';

class SugarTestOppLineItemUtilities
{
    protected static $_createdLines = array();

    private function __construct() {}

    public static function createLine($id = '')
    {
        $time = mt_rand();
        $name = 'SugarLine';
        $line = new OpportunityLine();
        $line->tax_class = 'Taxable';
        $line->price = '100.00';
        $line->discount_price = '100.00';
        $line->quantity = '100';
        if(!empty($id))
        {
            $line->new_with_id = true;
            $line->id = $id;
        }
        $line->save();
        self::$_createdLines[] = $line;
        return $line;
    }

    public static function setCreatedLine($line_ids)
    {
        foreach($line_ids as $line_id)
        {
            $line = new OpportunityLine();
            $line->id = $line_id;
            self::$_createdLines[] = $line;
        }
    }

    public static function removeAllCreatedLines()
    {
        $line_ids = self::getCreatedLineIds();
        $GLOBALS['db']->query('DELETE FROM opportunity_lines WHERE id IN (\'' . implode("', '", $line_ids) . '\')');
    }

    public static function getCreatedLineIds()
    {
        $line_ids = array();
        foreach (self::$_createdLines as $line)
        {
            $line_ids[] = $line->id;
        }
        return $line_ids;
    }
}
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


require_once 'modules/OpportunityLineBundles/OpportunityLineBundle.php';

class SugarTestOppLineBundleUtilities
{
    private static $_createdLineBundles = array();

    private function __construct() {}

    public static function createLineBundle($id = '')
    {
        $time = mt_rand();
        $name = 'SugarLineBundle';
        $linebundle = new OpportunityLineBundle();
        $linebundle->name = $name . $time;

        if(!empty($id))
        {
            $linebundle->new_with_id = true;
            $linebundle->id = $id;
        }
        $linebundle->save();
        self::$_createdLineBundles[] = $linebundle;
        return $linebundle;
    }

    public static function setCreatedLineBundle($linebundle_ids)
    {
        foreach($linebundle_ids as $linebundle_id)
        {
            $linebundle = new OpportunityLineBundle();
            $linebundle->id = $linebundle_id;
            self::$_createdLineBundles[] = $linebundle;
        }
    }

    public static function removeAllCreatedLineBundles()
    {
        $linebundle_ids = self::getCreatedLineBundleIds();
        $GLOBALS['db']->query('DELETE FROM opportunity_line_bundles WHERE id IN (\'' . implode("', '", $linebundle_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM opp_line_bundle_opp_line WHERE bundle_id IN (\'' . implode("', '", $linebundle_ids) . '\')');
    }

    public static function getCreatedLineBundleIds()
    {
        $linebundle_ids = array();
        foreach (self::$_createdLineBundles as $linebundle)
        {
            $linebundle_ids[] = $linebundle->id;
        }
        return $linebundle_ids;
    }
}
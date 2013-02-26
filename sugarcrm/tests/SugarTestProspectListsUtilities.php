<?php
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

require_once('modules/ProspectLists/ProspectList.php');

class SugarTestProspectListsUtilities
{
    private static $_aCreatedProspectLists = array();
    private static $_aCreatedProspectListsIds = array();

    private static $_createdProspectLists = array();

    /**
     * @static Creates a test prospectList
     * @param string $prospect_list_id
     */
    public static function createProspectLists($id = '')
    {
        $name = 'SugarProspectListName';

        $prospectList = new ProspectList();
        $prospectList->name = $name;

        if(!empty($id))
        {
            $prospectList->new_with_id = true;
            $prospectList->id = $id;
        }
        $prospectList->save();
        self::$_createdProspectLists[] = $prospectList;
        return $prospectList;
    }

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeProspectLists($prospect_list_id)
    {
        if (is_array($prospect_list_id)) {

            $prospect_list_id = implode("','", $prospect_list_id);
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('{$prospect_list_id}')");
        } else {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$prospect_list_id}'");
        }
    }

    /**
     * @static
     * @param string $prospect_list_id
     * @param string $prospect_id
     */
    public static function removeProspectsListToProspectRelation($prospect_list_id, $prospect_id)
    {

        $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE prospect_list_id='{$prospect_list_id}' AND related_id='{$prospect_id}'");
    }

    /**
     * @static
     */
    public static function removeAllCreatedProspectLists()
    {
        $prospectListIds = self::getCreatedProspectListIds();
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . implode("', '", $prospectListIds) . '\')');
    }

    /**
     * @static
     */
    public static function getCreatedProspectListIds()
    {
        $prospectListIds = array();
        foreach (self::$_createdProspectLists as $prospectList) {
            $prospectListIds[] = $prospectList->id;
        }
        return $prospectListIds;
    }

    public static function createProspectList($id = '', $aParams = array())
    {
        $time = mt_rand();
        $oProspectList = new ProspectList();
        $oProspectList->name = 'ProspectList' . $time;
        if (!empty($id))
        {
            $oProspectList->id = $id;
        }
        if (!empty($aParams))
        {
            foreach ($aParams as $key => $val)
            {
                $oProspectList->$key = $val;
            }
        }
        $oProspectList->save();
        self::$_aCreatedProspectLists[] = $oProspectList;
        self::$_aCreatedProspectListsIds[] = $oProspectList->id;
        return $oProspectList;
    }

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeCreatedProspectLists($id = '')
    {
        if (!empty($id))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$id}'");
        }
        elseif (!empty(self::$_aCreatedProspectLists))
        {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('" . implode("','", self::$_aCreatedProspectListsIds) . "')");
        }
    }

}

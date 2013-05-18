<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
********************************************************************************/

require_once 'modules/Cases/Case.php';

class SugarTestCaseUtilities
{
    private static $_createdCases = array();

    private function __construct()
    {}

    public static function createCase($id = '', $caseValues = array())
    {
        $time = mt_rand();
        $case = new aCase();

        if (isset($caseValues['name'])) {
            $case->name = $caseValues['name'];
        } else {
            $case->name = 'SugarCase' . $time;
        }

        if (!empty($id)) {
            $case->new_with_id = true;
            $case->id = $id;
        }
        $case->save();
        $GLOBALS['db']->commit();
        self::$_createdCases[] = $case;
        return $case;
    }

    public static function setCreatedCase($case_ids)
    {
        foreach ($case_ids as $case_id) {
            $case = new aCase();
            $case->id = $case_id;
            self::$_createdCases[] = $case;
        } // foreach
    } // fn
    public static function removeAllCreatedCases()
    {
        $case_ids = self::getCreatedCaseIds();
        $GLOBALS['db']->query('DELETE FROM cases WHERE id IN (\'' . implode("', '", $case_ids) . '\')');
    }

    public static function getCreatedCaseIds()
    {
        $case_ids = array();
        foreach (self::$_createdCases as $case) {
            $case_ids[] = $case->id;
        }
        return $case_ids;
    }
}

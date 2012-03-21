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

require_once('include/database/FreeTDSManager.php');
require_once('include/database/SqlSrvManager.php');

class Bug51311Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function providerBug51311()
    {
        return array(
            array(
                array (
                  'name' => 'contents',
                  'dbType' => 'nvarchar(max)',
                  'type' => 'nvarchar',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences'
            ),

            array(
                array (
                  'name' => 'contents',
                  'dbType'  => 'text',
                  'type' => 'nvarchar',
                  'vname' => 'LBL_DESCRIPTION',
                  'isnull' => true,
                ),
                'user_preferences'
            ),
        );
    }

    /**
     * @dataProvider providerBug51311
     */
    public function testFreeTDSMassageFieldDef($fieldDef, $tablename)
    {
        $manager = new FreeTDSManager();
        $this->assertTrue($manager->isTextType($fieldDef['dbType']));
    }

    /**
     * @dataProvider providerBug51311
     */
    public function testSqlSrvMassageFieldDef($fieldDef, $tablename)
    {
        $manager = new SqlsrvManager();
        $this->assertTrue($manager->isTextType($fieldDef['dbType']));
    }


}
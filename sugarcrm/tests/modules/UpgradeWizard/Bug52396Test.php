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

//FILE SUGARCRM flav=ent ONLY

require_once 'include/database/OracleManager.php';
require_once 'modules/UpgradeWizard/uw_utils.php';

/**
 * Bug #52396
 * Can't convert ent to ult with oracle 10 via wizard.
 *
 * @author mgusev@sugarcrm.com
 * @ticked 52396
 */
class Bug52396Test extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var OracleManager
     */
    protected $db;

    public function setUp()
    {
        if ($GLOBALS['db']->dbType != 'oci8')
        {
            $this->markTestSkipped('Oracle is required');
            return false;
        }

        parent::setUp();

        $this->db = $GLOBALS['db'];
        $GLOBALS['db'] = new Bug52396OracleManager();
    }

    public function tearDown()
    {
        if ($GLOBALS['db']->dbType != 'oci8')
        {
            return false;
        }
        unset($GLOBALS['db']);
        $GLOBALS['db'] = $this->db;
    }

    public function provideVersions()
    {
        return array(
            array('8.0.0', false),
            array('8.0.9', false),
            array('8.0.10', false),
            array('8.0.11', false),
            array('9.0.0', true),
            array('9.0.9', true),
            array('9.0.10', true),
            array('9.0.11', true),
            array('10.0.0', true),
            array('10.0.9', true),
            array('10.0.10', true),
            array('10.0.11', true),
            array('11.0.0', true),
            array('11.0.9', true),
            array('11.0.10', true),
            array('11.0.11', true),
            array('12.0.0', true),
            array('12.0.9', true),
            array('12.0.10', true),
            array('12.0.11', true)
        );
    }

    /**
     * Test tries to assert valid and invalid versions of Oracle
     *
     * @dataProvider provideVersions
     * @group 52396
     * @return void
     */
    public function testChangingOfRelation($version, $isValid)
    {
        $GLOBALS['db']->version = $version;
        $result = checkSystemCompliance();
        if ($isValid == true)
        {
            $this->assertArrayNotHasKey('ociVersion', $result, 'Version of oracle is valid but not passed');
        }
        else
        {
            $this->assertArrayHasKey('ociVersion', $result, 'Version of oracle is not valid but passed');
        }
    }
}

/**
 * Mock OracleManager to return required version for test
 */
class Bug52396OracleManager extends OracleManager
{
    /**
     * @var string
     */
    public $version = '';

    /**
     * Return faked version of Oracle
     * @return string
     */
    public function version()
    {
        return $this->version;
    }
}

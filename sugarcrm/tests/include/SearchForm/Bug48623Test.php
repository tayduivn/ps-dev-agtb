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

require_once('include/SearchForm/SearchForm2.php');

class Bug48623Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', 'EDT');
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @dataProvider dateTestProvider
     */
    public function testParseDateExpressionWithAndWithoutTimezoneAdjustment($expected1, $expected2, $operator, $type) {
        global $timedate;

        $seed = new Opportunity();
        $sf = new SearchForm2Wrap($seed, 'Opportunities', 'index');

        $where = $sf->publicParseDateExpression($operator, 'opportunities.date_closed', $type);
        $this->assertRegExp($expected1, $where);
        $this->assertRegExp($expected2, $where);
    }

    public function dateTestProvider() {
        $noTzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 00:00:00\'/';
        $noTzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 23:59:59\'/';
        $tzRegExp1 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[4,5]:00:00\'/';
        $tzRegExp2 = '/\'[0-9]{4}-[0-9]{2}-[0-9]{2} 0[3,4]:59:59\'/';
        return array(
            //  $expected1, expected2, $operator, $type
            array($noTzRegExp1, $noTzRegExp2, 'this_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_month', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'this_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_year', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'yesterday', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'today', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'tomorrow', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_7_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_7_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'last_30_days', 'date'),
            array($noTzRegExp1, $noTzRegExp2, 'next_30_days', 'date'),

            array($tzRegExp1, $tzRegExp2, 'this_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_month', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'this_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_year', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'yesterday', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'today', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'tomorrow', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_7_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_7_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'last_30_days', 'datetime'),
            array($tzRegExp1, $tzRegExp2, 'next_30_days', 'datetime'),
        );
    }

}


/**
 * Wrap the SearchForm class to make a protected function public
 */
class SearchForm2Wrap extends SearchForm {
    public function publicParseDateExpression($operator, $db_field, $field_type) {
        return $this->parseDateExpression($operator, $db_field, $field_type);
    }
}

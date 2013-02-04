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

class QuotaTests extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars','$','MOD',2.0);
    }

    public function tearDown()
    {
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestHelper::tearDown();
    }

    /*
     * Test that the base_rate field is populated with rate
     * of currency_id
     *
     */
    public function testQuotaRate()
    {
        $quota = SugarTestQuotaUtilities::createQuota(500);
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $quota->currency_id = $currency->id;
        $quota->save();
        $this->assertEquals(
            sprintf('%.6f',$quota->base_rate),
            sprintf('%.6f',$currency->conversion_rate)
        );
    }

    public function testGetRollupQuotaReturnsArrayForEmptyQuota()
    {
        $quota = SugarTestQuotaUtilities::createQuota();
        $quota->db = $this->getMock("DBManager", array(
            "quote",
            "convert",
            "fromConvert",
            "query",
            "freeDbResult",
            "renameColumnSQL",
            "get_indices",
            "get_columns",
            "add_drop_constraint",
            "getFieldsArray",
            "getTablesArray",
            "version",
            "tableExists",
            "fetchRow",
            "connect",
            "changeColumnSQL",
            "disconnect",
            "lastDbError",
            "validateQuery",
            "valid",
            "dbExists",
            "tablesLike",
            "createDatabase",
            "dropDatabase",
            "getDbInfo",
            "userExists",
            "createDbUser",
            "full_text_indexing_installed",
            "getFulltextQuery",
            "installConfig",
            "getGuidSQL",
            "limitQuery",
            "fetchByAssoc",
            "createTableSQLParams",
            "getFromDummyTable",
        ));
        $quota->db->expects($this->any())->method('limitQuery')->will($this->returnValue('foo'));
        $quota->db->expects($this->any())->method('fetchByAssoc')->will($this->returnValue(false));
        $this->assertEquals(
            array(
                'currency_id' => -99,
                'amount' => 0,
                'formatted_amount' => '$0.00',
            ),
            $quota->getRollupQuota(1)
        );
    }

    public function testGetRollupQuota()
    {
        $quota = SugarTestQuotaUtilities::createQuota(10);
        $quota->db = $this->getMock("DBManager", array(
            "quote",
            "convert",
            "fromConvert",
            "query",
            "limitQuery",
            "freeDbResult",
            "renameColumnSQL",
            "get_indices",
            "get_columns",
            "add_drop_constraint",
            "getFieldsArray",
            "getTablesArray",
            "version",
            "tableExists",
            "fetchRow",
            "connect",
            "changeColumnSQL",
            "disconnect",
            "lastDbError",
            "validateQuery",
            "valid",
            "dbExists",
            "tablesLike",
            "createDatabase",
            "dropDatabase",
            "getDbInfo",
            "userExists",
            "createDbUser",
            "full_text_indexing_installed",
            "getFulltextQuery",
            "installConfig",
            "getGuidSQL",
            "fetchByAssoc",
            "createTableSQLParams",
            "getFromDummyTable",
        ));
        $quota->db->expects($this->any())->method('limitQuery')->will($this->returnValue('foo'));
        $quota->db->expects($this->any())->method('fetchByAssoc')->will($this->returnValue(array(
            'currency_id' => -99,
            'amount' => 10,
        )));
        $this->assertEquals(
            array(
                'currency_id' => -99,
                'amount' => 10,
                'formatted_amount' => '$10.00',
            ),
            $quota->getRollupQuota(1)
        );
    }
}

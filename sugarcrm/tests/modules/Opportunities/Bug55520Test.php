<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/export_utils.php';

/**
 * Bug #55520
 * Export opportunities on windows corrupts unicode characters
 *
 * @author vromanenko@sugarcrm.com
 * @ticked 55520
 */
class Bug55520Test extends Sugar_PHPUnit_Framework_TestCase
{
    const BOM = "\xEF\xBB\xBF";
    const DEFAULT_EXPORT_CHARSET_PREF_NAME = 'default_export_charset';
    const UTF8_CHARSET = 'UTF-8';
    const NON_UTF8_CHARSET = 'ISO-8859-1';

    /**
     * @var Opportunity
     */
    protected $opportunity;

    /**
     * @var string
     */
    protected $defaultExportCharset;

    /**
     * @var User
     */
    protected $currentUser;

    protected function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->currentUser = $GLOBALS['current_user'];
        $this->defaultExportCharset = $this->currentUser->getPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME);

        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
    }

    /**
     * Ensure that exported data starts with BOM
     *
     * @group 55520
     */
    public function testExportStringIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::UTF8_CHARSET);
        $export = export('Opportunities', $this->opportunity->id);
        $this->assertStringStartsWith(self::BOM, $export);
    }

    /**
     * Ensure that exported data does not start with BOM if the export character set is other than utf-8
     *
     * @group 55520
     */
    public function testExportStringNotIncludesBOM()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, self::NON_UTF8_CHARSET);
        $export = export('Opportunities', $this->opportunity->id);
        $this->assertStringStartsNotWith(self::BOM, $export);
    }

    protected function tearDown()
    {
        $this->currentUser->setPreference(self::DEFAULT_EXPORT_CHARSET_PREF_NAME, $this->defaultExportCharset);
        SugarTestHelper::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

}
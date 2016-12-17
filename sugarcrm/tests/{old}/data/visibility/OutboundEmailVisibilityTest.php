<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php';

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass OutboundEmailVisibility
 * @group email
 */
class OutboundEmailVisibilityTest extends \Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('current_user');

        // Create a system account.
        OutboundEmailConfigurationTestHelper::setUp();

        // Create two user accounts for the current user.
        OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        // Create a system override account for the current user.
        OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );

        // Create one user account for another user.
        OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration(Uuid::uuid1());
    }

    public static function tearDownAfterClass()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function tearDown()
    {
        OutboundEmailConfigurationTestHelper::restoreAllowDefaultOutbound();
        parent::tearDown();
    }

    public function addVisibilityProvider()
    {
        return [
            [0, 3],
            [1, 3],
            [2, 3],
        ];
    }

    /**
     * @covers ::addVisibilityWhere
     * @dataProvider addVisibilityProvider
     * @param int $allowDefaultOutbound The notify_allow_default_outbound setting.
     * @param int $expected The number of accounts that should be returned.
     */
    public function testAddVisibilityWhere($allowDefaultOutbound, $expected)
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound($allowDefaultOutbound);

        $bean = BeanFactory::newBean('OutboundEmail');
        $accounts = (array) $bean->get_full_list();

        $this->assertCount($expected, $accounts, "{$expected} accounts should have been returned");
    }

    /**
     * @covers ::addVisibilityWhereQuery
     * @dataProvider addVisibilityProvider
     * @param int $allowDefaultOutbound The notify_allow_default_outbound setting.
     * @param int $expected The number of accounts that should be returned.
     */
    public function testAddVisibilityWhereQuery($allowDefaultOutbound, $expected)
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound($allowDefaultOutbound);

        $bean = BeanFactory::newBean('OutboundEmail');

        $q = new SugarQuery();
        $q->select('id');
        $q->from($bean);
        $accounts = $bean->fetchFromQuery($q);

        $this->assertCount($expected, $accounts, "{$expected} accounts should have been returned");
    }
}

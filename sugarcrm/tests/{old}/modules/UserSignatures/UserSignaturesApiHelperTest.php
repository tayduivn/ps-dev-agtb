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

require_once 'modules/UserSignatures/UserSignaturesApiHelper.php';
require_once 'include/api/RestService.php';

class UserSignaturesApiHelperTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $bean;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('current_user');

        $this->bean = BeanFactory::newBean('UserSignatures');
        $this->bean->id = create_guid();
    }

    public function tearDown()
    {
        unset($this->bean);
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testFormatForApi_NoDefault_IsDefaultFalse()
    {
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertFalse($data['is_default']);
    }

    public function testFormatForApi_DefaultDoesNotMatchId_IsDefaultTrue()
    {
        global $current_user;
        $current_user->setPreference('signature_default', 'not_my_id');
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertFalse($data['is_default']);
    }

    public function testFormatForApi_DefaultMatchesId_IsDefaultTrue()
    {
        global $current_user;
        $current_user->setPreference('signature_default', $this->bean->id);
        $helper = new UserSignaturesApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $data = $helper->formatForApi($this->bean);
        $this->assertTrue($data['is_default']);
    }
}

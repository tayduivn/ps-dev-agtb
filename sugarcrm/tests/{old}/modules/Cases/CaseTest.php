<?php
//FILE SUGARCRM flav=ent ONLY
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

use PHPUnit\Framework\TestCase;

class CaseTest extends TestCase
{
    private $case;

    public function tearDown()
    {
        unset($this->case);
    }

    public static function tearDownAfterClass()
    {
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * Test whether save method update resolved_datetime when Case is resolved
     * @param string $status The status of a case
     * @param bool $emptyResolvedDate Whether resolved_datetime should be empty
     * @dataProvider updateResolvedDateOnSaveProvider
     */
    public function testUpdateResolvedDateOnSave(string $status, bool $emptyResolvedDate)
    {
        $this->case = SugarTestCaseUtilities::createCase(null, ['status' => $status]);
        $this->case->save();
        $this->assertSame($emptyResolvedDate, empty($this->case->resolved_datetime));
    }

    public function updateResolvedDateOnSaveProvider(): array
    {
        return [
            ['New', true],
            ['Assigned', true],
            ['Closed', false],
            ['Pending Input', true],
            ['Rejected', false],
            ['Duplicate', false],
        ];
    }

    /**
     * Test whether save method update business center from related account when empty
     * @param null|string $businessCenterId The id of business center
     * @param string $accountId The id of account
     * @param null|string $relatedBusinessCenterId The id of business center on a related account
     * @param null|string $expectedBusinessCenterId The expected id of business center on the case
     * @dataProvider updateBusinessCenterOnSaveProvider
     */
    public function testUpdateBusinessCenterOnSave(?string $businessCenterId, string $accountId, ?string $relatedBusinessCenterId, ?string $expectedBusinessCenterId)
    {
        $this->relatedAccount = SugarTestAccountUtilities::createAccount(null, [
            'id' => $accountId,
            'business_center_id' => $relatedBusinessCenterId,
        ]);
        $this->relatedAccount->save();
        $this->case = SugarTestCaseUtilities::createCase(null, [
            'account_id' => $accountId,
            'business_center_id' => $businessCenterId,
        ]);
        $this->case->save();
        $this->assertSame($this->case->business_center_id, $expectedBusinessCenterId);
    }

    public function updateBusinessCenterOnSaveProvider(): array
    {
        return [
            ['1234', '9999', '4321', '1234'],
            [null, '9999', '4321', '4321'],
            [null, '9999', null, null],
        ];
    }
}

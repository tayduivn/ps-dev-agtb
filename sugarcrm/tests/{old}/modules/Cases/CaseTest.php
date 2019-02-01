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
}

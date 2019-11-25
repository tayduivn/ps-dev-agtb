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

class IssueTest extends TestCase
{
    protected $cases = [];
    protected $changeTimers = [];

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        if (!empty($this->changeTimers)) {
            $GLOBALS['db']->query('DELETE FROM changetimers WHERE id IN (\'' . implode("', '", $this->changeTimers) . '\')');
        }
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
    }

    public function testSave()
    {
        $properties = [
            'assigned_user_id' => '1',
            'status' => 'New',
        ];
        $case = SugarTestCaseUtilities::createCase(null, $properties);
        $this->cases[] = $case->id;
        global $db;
        $sql = 'SELECT id, field_name, value_string FROM changetimers WHERE parent_id=' . $db->quoted($case->id);
        $rows = $db->query($sql);
        $results = [];
        while ($rows && $row = $db->fetchByAssoc($rows)) {
            $results[$row['field_name']] = $row['value_string'];
            $this->changeTimers[] = $row['id'];
        }
        $this->assertEquals($properties, $results);
    }

    public function testCreateNewCTRecord()
    {
        $case = new CaseMock();
        $case->id = '1234567';
        $case->status = 'Assigned';
        $now = TimeDate::getInstance()->nowDb();
        $case->date_modified = $now;

        $id = $case->createNewCTRecordMock('status');

        $this->assertNotEmpty($id);
        $this->changeTimers[] = $id;

        $stBean = BeanFactory::retrieveBean('ChangeTimers', $id);
        $this->assertSame($stBean->parent_type, 'Cases');
        $this->assertSame($stBean->parent_id, $case->id);
        $this->assertSame($stBean->field_name, 'status');
        $this->assertSame($stBean->value_string, 'Assigned');
        $this->assertSame($stBean->date_modified, $now);
    }

    /**
     * @param $isUpdate
     * @param $values
     * @param $fields
     * @param $expected
     * @dataProvider getCTFieldsToProcessProvider
     */
    public function testGetCTFieldsToProcess(bool $isUpdate, array $values, array $fields, array $expected)
    {
        $case = new CaseMock();
        $case->dataChanges = $values;
        foreach ($values as $key => $value) {
            $case->$key = $value;
        }
        $fields = $case->getCTFieldsToProcessMock($fields, $isUpdate);
        $this->assertSame($expected, $fields);
    }

    public function getCTFieldsToProcessProvider(): array
    {
        return [
            [
                false,
                ['status' => 'New', 'assigned_user_id' => 1],
                ['status', 'assigned_user_id'],
                ['status', 'assigned_user_id'],
            ],
            [
                false,
                ['status' => 'New'],
                ['status', 'assigned_user_id'],
                ['status'],
            ],
            [
                false,
                ['status' => 'New', 'assigned_user_id' => 1],
                ['status'],
                ['status'],
            ],
            [
                true,
                ['status' => 'New', 'assigned_user_id' => 1],
                ['status', 'assigned_user_id'],
                ['status', 'assigned_user_id'],
            ],
            [
                true,
                ['status' => 'New'],
                ['status', 'assigned_user_id'],
                ['status'],
            ],
            [
                true,
                ['status' => 'New', 'assigned_user_id' => 1],
                ['status'],
                ['status'],
            ],
        ];
    }
}

class CaseMock extends aCase
{
    public function getCTFieldsToProcessMock(array $fields, bool $isUpdate) : array
    {
        return parent::getCTFieldsToProcess($fields, $isUpdate);
    }

    public function createNewCTRecordMock(string $field)
    {
        return parent::createNewCTRecord($field);
    }
}

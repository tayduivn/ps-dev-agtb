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

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/3_CallsMeetingsUntilDate.php';

/**
 * Class CallsMeetingsUpdateUntilTest
 *
 * @covers SugarUpgradeCallsMeetingsUntilDate
 */
class CallsMeetingsUpdateUntilTest extends UpgradeTestCase
{
    /** @var  PHPUnit_Framework_MockObject_MockObject | SugarUpgradeCallsMeetingsUntilDate $scriptMock */
    protected $scriptMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject | UpgradeDriver $upgraderMock */
    protected $upgraderMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject | SugarQuery $queryMock */
    protected $queryMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject | SugarBean $beanMock */
    protected $beanMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject | DBManager $dbMock */
    protected $dbMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->upgraderMock = $this->getMock('UpgradeDriver');
        $this->beanMock = $this->getMock('SugarBean');
        $this->queryMock = $this->getMock('SugarQuery');
        $this->dbMock = $this->getMock('DBManager');

        $this->scriptMock = $this->getMock(
            'SugarUpgradeCallsMeetingsUntilDate',
            array('getSugarQuery', 'getSugarBean', 'getUserBean'),
            array($this->upgraderMock)
        );
        $this->scriptMock->method('getSugarQuery')->willReturn($this->queryMock);
        $this->scriptMock->db = $this->dbMock;

        /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject $sugarQueryBuilderAndWhere */
        $sugarQueryBuilderAndWhere = $this->getMock('SugarQuery_Builder_Andwhere', array(), array(), '', false);
        $this->queryMock->method('where')->willReturn($sugarQueryBuilderAndWhere);
    }

    /**
     * Provides upgrade version, db fetch info and result info for checking.
     *
     * @return array
     */
    public function runProvider()
    {
        $untilMinsk = new SugarDateTime('2016-03-19 23:59:00', new DateTimeZone('Europe/Minsk'));
        $untilBerlin = new SugarDateTime('2016-03-26 23:59:00', new DateTimeZone('Europe/Berlin'));

        return array(
            array(
                'module' => 'Meetings',
                'table' => 'meetings',
                'queryResult' => array(
                    array(
                        'id' => '1957f9df-c129-e9f9-6bbb-56e91fa46c7f',
                        'created_by' => 'seed_sally_id',
                        'repeat_until' => '2016-03-26 00:00:00',
                    ),
                    array(
                        'id' => '3aa10393-4f70-1968-3c45-56e91fde76ea',
                        'created_by' => '1',
                        'repeat_until' => '2016-03-19 00:00:00',
                    ),
                    array(
                        'id' => '3aa10393-4f70-1968-3c45-56e91fde76ea',
                        'created_by' => '1',
                        'repeat_until' => null,
                    ),
                ),
                'usersTimezonesInfo' => array(
                    '1' => 'Europe/Minsk',
                    'seed_sally_id' => 'Europe/Berlin'
                ),
                'expectedUpdateParams' => array(
                    array(
                        'meetings',
                        null,
                        array('repeat_until' => $untilBerlin->asDb()),
                        array('id' => '1957f9df-c129-e9f9-6bbb-56e91fa46c7f'),
                        null,
                        true,
                        true,
                    ),
                    array(
                        'meetings',
                        null,
                        array('repeat_until' => $untilMinsk->asDb()),
                        array('id' => '3aa10393-4f70-1968-3c45-56e91fde76ea'),
                        null,
                        true,
                        true,
                    ),
                ),
                'fromVersion' => '7.7',
                'callCount' => 1,
            ),
            array(
                'module' => 'Meetings',
                'table' => 'meetings',
                'queryResult' => array(),
                'usersTimezonesInfo' => array(
                    '1' => 'Europe/Minsk',
                    'seed_sally_id' => 'Europe/Berlin'
                ),
                'expectedUpdateParams' => array(),
                'fromVersion' => '7.8',
                'callCount' => 0,
            ),
            array(
                'module' => 'Meetings',
                'table' => 'meetings',
                'queryResult' => array(
                    array(
                        'id' => '1957f9df-c129-e9f9-6bbb-56e91fa46c7f',
                        'created_by' => 'seed_sally_id',
                        'repeat_until' => '2016-03-26 00:00:00.000000',
                    ),
                ),
                'usersTimezonesInfo' => array(
                    '1' => 'Europe/Minsk',
                    'seed_sally_id' => 'Europe/Berlin'
                ),
                'expectedUpdateParams' => array(
                    array(
                        'meetings',
                        null,
                        array('repeat_until' => $untilBerlin->asDb()),
                        array('id' => '1957f9df-c129-e9f9-6bbb-56e91fa46c7f'),
                        null,
                        true,
                        true,
                    ),
                ),
                'fromVersion' => '7.8RC2',
                'callCount' => 1,
            ),
        );
    }

    /**
     * Test for upgrade process.
     *
     * @param string $module
     * @param string $table
     * @param array $queryResult
     * @param array $timezonesInfo
     * @param array $expectedUpdateParams
     * @param string $fromVersion
     * @param int $callCount
     *
     * @covers       SugarUpgradeCallsMeetingsUntilDate::run
     *
     * @dataProvider runProvider
     */
    public function testRun(
        $module,
        $table,
        array $queryResult,
        array $timezonesInfo,
        array $expectedUpdateParams,
        $fromVersion,
        $callCount
    ) {
        $this->upgraderMock->from_version = $fromVersion;
        \SugarTestReflection::setProtectedValue($this->scriptMock, 'modulesForUpgrade', array($module));

        $this->beanMock->table_name = $table;
        $this->scriptMock->expects($this->exactly($callCount))->method('getSugarBean')->with($module)
                         ->willReturn($this->beanMock);
        $this->queryMock->expects($this->exactly($callCount))->method('execute')->willReturn($queryResult);

        $usersMap = array();
        $dateTimeMap = array();
        foreach ($queryResult as $row) {
            /** @var User|PHPUnit_Framework_MockObject_MockObject $userMock */
            $userMock = $this->getMock('User');
            $userMock->method('getPreference')->with('timezone')->willReturn($timezonesInfo[$row['created_by']]);
            $usersMap[] = array($row['created_by'], $userMock);
            $dateTimeMap[] = array($row['repeat_until'], 'datetime', substr($row['repeat_until'], 0, 19));
        }
        $this->scriptMock->method('getUserBean')->willReturnMap($usersMap);

        $expectedCount = count($expectedUpdateParams);
        $this->dbMock->expects($this->exactly($expectedCount))->method('updateParams');
        $this->dbMock->expects($this->exactly($expectedCount))->method('fromConvert')->willReturnMap($dateTimeMap);
        $k = 0;
        for ($i = 0; $i < 2 * $expectedCount; $i ++) {
            if ($i & 1) {
                $this->dbMock->expects($this->at($i))->method('updateParams')->willReturnCallback(function () use (
                    $expectedUpdateParams,
                    $k
                ) {
                    $this->assertEquals($expectedUpdateParams[$k], func_get_args());
                });
                $k ++;
            }
        }
        $this->scriptMock->run();
    }
}

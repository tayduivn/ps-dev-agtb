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

namespace Sugarcrm\SugarcrmTests\upgrade\scripts\post;

require_once 'tests/upgrade/UpgradeTestCase.php';
require_once 'upgrade/scripts/post/9_ParticipantsLinksUpdate.php';

/**
 * Class SugarUpgradeParticipantsLinksUpdateTest
 * @covers SugarUpgradeParticipantsLinksUpdate
 */
class SugarUpgradeParticipantsLinksUpdateTest extends \UpgradeTestCase
{
    /**
     * Data participants for before and after conversion.
     *
     * @return array
     */
    public static function participantsLinksUpdateProvider()
    {
        $randomDataHashes = array(
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(1000, 1999), 'TestUser' . rand(1000, 1999)),
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(2000, 2999), 'TestUser' . rand(2000, 2999)),
            array(\Sugarcrm\Sugarcrm\Util\Uuid::uuid1(), rand(3000, 3999), 'TestUser' . rand(3000, 3999)),
        );

        $userList = array(
            $randomDataHashes[0][0] => $randomDataHashes[0][2],
            $randomDataHashes[1][0] => $randomDataHashes[1][2],
            $randomDataHashes[2][0] => $randomDataHashes[2][2],
        );

        return array(
            array(
                'executeReturn' => array(
                    array(
                        'id' => \Sugarcrm\Sugarcrm\Util\Uuid::uuid1(),
                        'participants_links' => json_encode(array(
                            'test_' . $randomDataHashes[0][1] . '@test.com' => array(
                                'beanName' => 'Contacts',
                                'beanId' => $randomDataHashes[0][0],
                            ),
                            'test_' . $randomDataHashes[1][1] . '@test.com' => array(
                                'beanName' => 'Leads',
                                'beanId' => $randomDataHashes[1][0],
                            ),
                            'test_' . $randomDataHashes[2][1] . '@test.com' => array(
                                'beanName' => 'Users',
                                'beanId' => $randomDataHashes[2][0],
                            ),
                        )),
                    ),
                ),
                'after' => array(
                    array(
                        'parent' => array(
                            array(
                                'beanName' => 'Contacts',
                                'beanId' => $randomDataHashes[0][0],
                                'email' => 'test_' . $randomDataHashes[0][1] . '@test.com',
                                'displayName' => $randomDataHashes[0][2],
                            ),
                            array(
                                'beanName' => 'Leads',
                                'beanId' => $randomDataHashes[1][0],
                                'email' => 'test_' . $randomDataHashes[1][1] . '@test.com',
                                'displayName' => $randomDataHashes[1][2],
                            ),
                            array(
                                'beanName' => 'Users',
                                'beanId' => $randomDataHashes[2][0],
                                'email' => 'test_' . $randomDataHashes[2][1] . '@test.com',
                                'displayName' => $randomDataHashes[2][2],
                            ),
                        ),
                    ),
                ),
                'userList' => $userList,
            ),
        );
    }

    /**
     * Check update participants links.
     *
     * @dataProvider participantsLinksUpdateProvider
     * @param array $executeReturn
     * @param array $after
     * @param array $userList
     */
    public function testParticipantsLinksUpdate(array $executeReturn, array $after, array $userList)
    {
        $queryMock = $this->getMock('SugarQuery', array('execute'));
        $queryMock->method('execute')->willReturn($executeReturn);
        $dbMock = $this->getMock('DBManager');

        $count  = count($executeReturn);

        for ($i = 0; $i < $count; $i ++) {
            $dbMock->expects($this->at($i))->method('updateParams')->willReturnCallback(function () use ($after, $i) {
                $this->assertEquals(array('participants_links' => json_encode($after[$i])), func_get_arg(2));
            });
        }

        /** @var SugarUpgradeParticipantsLinksUpdate|PHPUnit_Framework_MockObject_MockObject $updateScriptMock */
        $updateScriptMock = $this->getMock(
            'SugarUpgradeParticipantsLinksUpdate',
            array('getQuery', 'createFormatDisplayName'),
            array($this->upgrader)
        );
        $updateScriptMock->db = $dbMock;
        $updateScriptMock->method('getQuery')->willReturn($queryMock);
        $updateScriptMock->method('createFormatDisplayName')
            ->willReturnCallback(function ($participant) use ($userList) {
                return $userList[$participant['beanId']];
            });
        $updateScriptMock->run();
    }
}

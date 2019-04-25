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

namespace Sugarcrm\SugarcrmTestUnit\inc\AccessControl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\AccessControl\AccessControlManager;
use Sugarcrm\Sugarcrm\AccessControl\SugarFieldVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SugarFieldVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SugarFieldVoter
 */
class SugarFieldVoterTest extends TestCase
{
    /**
     * @covers ::supports
     * @dataProvider supportsProvider
     */
    public function testSupports($access_config, $subject, $expected)
    {
        $voterMock = $this->getMockBuilder(SugarFieldVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProtectedList'])
            ->getMock();

        $voterMock->expects($this->any())
                ->method('getProtectedList')
                ->will($this->returnValue($access_config));

        $this->assertSame($expected, TestReflection::callProtectedMethod($voterMock, 'supports', [[], $subject]));
    }

    public function supportsProvider()
    {
        return [
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1']],
                true,
            ],
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1_no_in_the_list']],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => ['field1', 'not string field']]],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::MODULES_KEY=> 'module_name'],
                false,
            ],
        ];
    }

    /**
     * @covers ::voteOnAttribute
     * @dataProvider voteOnAttributeProvider
     */
    public function testVoteOnAttribute($accessConfig, $subject, $entitled, $expected)
    {
        $voter = $this->getMockBuilder(SugarFieldVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions', 'getProtectedList'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $voter->expects($this->any())
            ->method('getProtectedList')
            ->will($this->returnValue($accessConfig));

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $expected,
            TestReflection::callProtectedMethod($voter, 'voteOnAttribute', ['', $subject, $tokenMock])
        );
    }

    public function voteOnAttributeProvider()
    {
        return [
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1']],
                ['SUGAR_SERVE'],
                true,
            ],
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1_no_in_the_list']],
                ['SUGAR_SERVE'],
                false,
            ],
            [
                ['ACL_MODULE_NAME' => ['field1' => ['SUGAR_SERVE']]],
                [AccessControlManager::FIELDS_KEY => ['ACL_MODULE_NAME' => 'field1']],
                ['NOT_SERVICE_CCLOUD'],
                false,
            ],
        ];
    }
}

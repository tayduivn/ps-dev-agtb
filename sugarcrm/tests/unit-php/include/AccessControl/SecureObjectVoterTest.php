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
use Sugarcrm\Sugarcrm\AccessControl\SecureObjectInterface;
use Sugarcrm\Sugarcrm\AccessControl\SecureObjectVoter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class SecureObjectVoterTest
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\AccessControl\SecureObjectVoter
 */
class SecureObjectVoterTest extends TestCase
{
    /**
     * @covers ::supports
     * @dataProvider supportsProvider
     */
    public function testSupports($isSecureObject, $expected)
    {
        if ($isSecureObject) {
            $mock = $this->getMockBuilder(SecureObjectInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['allowAccess'])
                ->getMock();

            $mock->expects($this->any())
                ->method('allowAccess')
                ->will($this->returnValue(true));
        } else {
            $mock = new SecureObjectVoter();
        }
        $voter = new SecureObjectVoter();
        $this->assertSame($expected, TestReflection::callProtectedMethod($voter, 'supports', [[], $mock]));
    }

    public function supportsProvider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @covers ::voteOnAttribute
     * @dataProvider voteOnAttributeProvider
     */
    public function testVoteOnAttribute($allowed, $entitled, $expected)
    {
        $secureObjMock = $this->getMockBuilder(SecureObjectInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['allowAccess'])
            ->getMock();

        $secureObjMock->expects($this->any())
            ->method('allowAccess')
            ->will($this->returnValue($allowed));

        $voter = $this->getMockBuilder(SecureObjectVoter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentUserSubscriptions'])
            ->getMock();

        $voter->expects($this->any())
            ->method('getCurrentUserSubscriptions')
            ->will($this->returnValue($entitled));

        $tokenMock = $this->getMockBuilder(TokenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame(
            $expected,
            TestReflection::callProtectedMethod($voter, 'voteOnAttribute', ['', $secureObjMock, $tokenMock])
        );
    }

    public function voteOnAttributeProvider()
    {
        return [
            [['SUGAR_SERVE'], ['SUGAR_SERVE'], true],
            [['SUGAR_SERVE'], [''], false],
            [['SUGAR_SERVE'], ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'], true],
            [['INVLIAD_SERVICE_CLOUD'], ['NOT_SERVICE_CLOUD', 'SUGAR_SERVE'], false],
        ];
    }
}

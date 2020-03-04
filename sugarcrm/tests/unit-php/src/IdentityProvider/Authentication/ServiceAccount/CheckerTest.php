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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\ServiceAccount;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\Checker;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\ServiceAccount\Checker
 */
class CheckerTest extends TestCase
{
    /**
     * @covers ::isAllowed()
     * @dataProvider isAllowedDataProvider
     */
    public function testIsAllowed($expected, $accessTokenInfo, $config)
    {
        $checker = new Checker($config);
        $this->assertEquals($expected, $checker->isAllowed($accessTokenInfo));
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider(): array
    {
        return [
            'emptySAs' => [
                false,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000002:tenant',
                ],
            ],
            'notAllowedSAs' => [
                false,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000003:tenant',
                    'allowedSAs' => ['srn:cluster:idm:eu:0000000004:tenant'],
                ],
            ],
            'allowedSAs' => [
                true,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000003:tenant',
                    'allowedSAs' => ['srn:cluster:idm:eu:0000000001:tenant'],
                ],
            ],
            'notAllowedForDifferentSubjectTenant' => [
                false,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000002:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
            ],
            'allowedForSameOwnTenantAndSATokenTenant' => [
                true,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
            ],
            'sameTenantIsCheckedEvenIfNotInAllowedSAs' => [
                true,
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:0000000001:tenant',
                    'allowedSAs' => ['srn:cluster:idm:eu:enabled:tenant'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function badOrMissingSRNsIsAllowedDataProvider(): array
    {
        return [
            'empty' => [
                [],
                [],
            ],
            'noOwnTenant' => [
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [],
            ],
            'incorrectOwnTID' => [
                [
                    'sub' => 'srn:cluster:idm:eu:0000000001:tenant',
                ],
                [
                    'tid' => 'srn:cluster:idm:eu:WRONG:tenant',
                ],
            ],
        ];
    }

    /**
     * @covers ::isAllowed()
     * @dataProvider badOrMissingSRNsIsAllowedDataProvider
     */
    public function testIsAllowedThrowsExceptionWhenGivenInvalidSRNs($accessTokenInfo, $config)
    {
        $this->expectException(InvalidArgumentException::class);
        $checker = new Checker($config);
        $checker->isAllowed($accessTokenInfo);
    }
}

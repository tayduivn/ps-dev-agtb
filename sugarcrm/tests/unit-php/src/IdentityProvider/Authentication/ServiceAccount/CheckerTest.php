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
    public function testIsAllowed($expected, $srn, $config)
    {
        $checker = $this->getMockBuilder(Checker::class)
            ->setConstructorArgs([$config])
            ->getMock();
        $this->assertEquals($expected, $checker->isAllowed($srn));
    }

    /**
     * @return array
     */
    public function isAllowedDataProvider(): array
    {
        return [
            'empty' => [
                false,
                '',
                [],
            ],
            'emptySA' => [
                false,
                '',
                ['allowedSAs' => ['srn:sa:enabled']],
            ],
            'emptyAllowed' => [
                false,
                'srn:sa:something',
                ['allowedSAs' => []],
            ],
            'notAllowed' => [
                false,
                'srn:sa:notAllowed',
                ['allowedSAs' => ['srn:sa:enabled']],
            ],
            'allowed' => [
                false,
                'srn:sa:enabled',
                ['allowedSAs' => ['srn:sa:enabled']],
            ],
        ];
    }
}

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

namespace Sugarcrm\SugarcrmTestUnit\modules\Users;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \User
 */
class UserTest extends TestCase
{

    /**
     * @covers ::getLicenseTypes
     * @covers ::processLicenseTypes
     *
     * @dataProvider getLicenseTypesProvider
     */
    public function testGetLicenseTypes($licenseType, $expected)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $userMock->license_type = $licenseType;
        $this->assertSame($expected, $userMock->getLicenseTypes());
    }

    public function getLicenseTypesProvider()
    {
        return [
            'License type is empty' => [
                    '',
                    [],
                ],
            'License type is null' => [
                    null,
                    [],
                ],
            'License type is in json encoded empty arry' => [
                    json_encode([]),
                    [],
                ],
            'License type is valid' => [
                    ['SUGAR_SELL', 'SUGAR_SERVE'],
                    ['SUGAR_SELL', 'SUGAR_SERVE'],
                ],
            'License type is in json encoded format' => [
                    json_encode(['SUGAR_SELL', 'SUGAR_SERVE']),
                    ['SUGAR_SELL', 'SUGAR_SERVE'],
                ],
            'License type is in json encoded format in single value' => [
                    json_encode(['SUGAR_SELL']),
                    ['SUGAR_SELL'],
                ],
        ];
    }

    /**
     * @covers ::processLicenseTypes
     *
     * @expectedException \SugarApiExceptionInvalidParameter
     *
     * @dataProvider processLicenseTypesExceptionProvider
     */
    public function testProcessLicenseTypesException($value)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $userMock->processLicenseTypes($value);
    }

    public function processLicenseTypesExceptionProvider()
    {
        return [
            'input is not string or array' => [true],
            'input is string but not a valid json encoded' => ['string format'],
        ];
    }
    /**
     * @covers ::validateLicenseTypes
     *
     * @dataProvider getValidateTypesProvider
     */
    public function testValidateLicenseTypes($source, $systemLicenseTypes, $allowEmpty, $expected)
    {
        $userMock = $this->getMockBuilder('\User')
            ->disableOriginalConstructor()
            ->setMethods(['getSystemSubscriptionKeys'])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getSystemSubscriptionKeys')
            ->willReturn($systemLicenseTypes);

        $licenseTypes = $userMock->processLicenseTypes($source);
        $this->assertSame($expected, $userMock->validateLicenseTypes($licenseTypes, $allowEmpty));
    }

    public function getValidateTypesProvider()
    {
        return [
            'License type is invalid' => [
                    ['invalid_license_type'],
                    ['SUGAR_SELL'],
                    true,
                    false,
                ],
            'License type is empty and empty license type is allowed' => [
                    '',
                    ['SUGAR_SELL'],
                    true,
                    true,
                ],
            'License type is null and empty license type is allowed' => [
                null,
                ['SUGAR_SELL'],
                false,
                false,
            ],
            'Empty license type and empty license type is not allowed' => [
                '',
                ['SUGAR_SELL'],
                    false,
                    false,
                ],
            'License type is not in current instance\'s subscriptions' => [
                    ['SUGAR_SERVE'],
                    ['SUGAR_SELL'],
                    false,
                    false,
                ],
            'License type is valid' => [
                    ['SUGAR_SELL'],
                    ['SUGAR_SELL'],
                    false,
                    true,
                ],
            'License type is one of system subscriptions' => [
                    ['SUGAR_SELL'],
                    ['SUGAR_SELL', 'SUGAR_SERVE'],
                    true,
                    true,
                ],
            'License type is in json encoded format' =>[
                json_encode(['SUGAR_SERVE', 'SUGAR_SELL']),
                ['SUGAR_SELL', 'SUGAR_SERVE'],
                true,
                true,
            ],
        ];
    }
}
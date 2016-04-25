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

namespace Sugarcrm\SugarcrmTests\Dav\Cal\Agent;

use Sugarcrm\Sugarcrm\Dav\Cal\Agent\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider data for a test the checking is validating info of client.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Agent\ValidatorTest::testValidateUserAgent
     * @return array
     */
    public static function clientInfoProvider()
    {
        return array(
            'notSetSupportedClients(MacOSX)' => array(
                'clientInfo' => array(
                    'platformName' => 'Mac OS X',
                    'platformVersion' => '10.11.4',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '361.2',
                ),
                'supportedClients' => null,
                'excepted' => true,
            ),
            'notSetSupportedClients(iOS)' => array(
                'clientInfo' => array(
                    'platformName' => 'iOS',
                    'platformVersion' => '9.3.1',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '1.0',
                ),
                'supportedClients' => null,
                'excepted' => true,
            ),
            'notSetSupportedClients(Widows)' => array(
                'clientInfo' => array(
                    'platformName' => 'Windows',
                    'platformVersion' => '10',
                    'clientName' => 'Microsoft Outlook',
                    'clientVersion' => '16.0.6326',
                ),
                'supportedClients' => null,
                'excepted' => false,
            ),
            'validateByOsName' => array(
                'clientInfo' => array(
                    'platformName' => 'Windows',
                    'platformVersion' => '10',
                    'clientName' => 'Microsoft Outlook',
                    'clientVersion' => '16.0.6326',
                ),
                'supportedClients' => array(
                    array('Windows', null, null, null, null, null),
                ),
                'excepted' => true,
            ),
            'validateByOsNameAndOsVersion(true)' => array(
                'clientInfo' => array(
                    'platformName' => 'iOS',
                    'platformVersion' => '9.3.3',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '1.0',
                ),
                'supportedClients' => array(
                    array('iOS', '=', '9.3.3', null, null, null),
                ),
                'excepted' => true,
            ),
            'validateByOsNameAndOsVersion(false)' => array(
                'clientInfo' => array(
                    'platformName' => 'iOS',
                    'platformVersion' => '9.3.2',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '1.0',
                ),
                'supportedClients' => array(
                    array('iOS', '>=', '9.3.3', null, null, null),
                ),
                'excepted' => false,
            ),
            'validateByOsNameAndOsVersionAndClientName' => array(
                'clientInfo' => array(
                    'platformName' => 'iOS',
                    'platformVersion' => '9.3.3',
                    'clientName' => 'CustomClient',
                    'clientVersion' => '1.0',
                ),
                'supportedClients' => array(
                    array('iOS', '=', '9.3.3', 'CustomClient', null, null),
                ),
                'excepted' => true,
            ),
            'validateByOsNameAndClientNameAndClientVersion' => array(
                'clientInfo' => array(
                    'platformName' => 'Linux',
                    'platformVersion' => '',
                    'clientName' => 'CustomClient',
                    'clientVersion' => '1.5',
                ),
                'supportedClients' => array(
                    array('Linux', null, null, 'CustomClient', '>', '1.4.9'),
                ),
                'excepted' => true,
            ),
            'validateAll' => array(
                'clientInfo' => array(
                    'platformName' => 'Windows',
                    'platformVersion' => '10',
                    'clientName' => 'Microsoft Outlook',
                    'clientVersion' => '16.0.6326',
                ),
                'supportedClients' => array(
                    array('Windows', '>=', '8.1', 'Microsoft Outlook', '<', '17'),
                ),
                'excepted' => true,
            ),
        );
    }

    /**
     * Checking is validate info of client.
     *
     * @dataProvider Sugarcrm\SugarcrmTests\Dav\Cal\Agent\ValidatorTest::clientInfoProvider
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Validator::isSupported
     * @param array $clientInfo
     * @param array|null $supportedClients
     * @param bool $excepted
     */
    public function testValidateUserAgent(array $clientInfo, $supportedClients, $excepted)
    {
        $validator = new Validator();

        if ($supportedClients !== null) {
            $validator->setSupportedClients($supportedClients);
        }

        $this->assertEquals($excepted, $validator->isSupported($clientInfo));
    }
}

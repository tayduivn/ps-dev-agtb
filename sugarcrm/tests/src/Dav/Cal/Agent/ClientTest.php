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

use Sugarcrm\Sugarcrm\Dav\Cal\Agent\Client;

/**
 * Class ClientTest
 * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider data for test detect client by user-agent.
     *
     * @see Sugarcrm\SugarcrmTests\Dav\Cal\Agent\ClientTest::testDetectUserClient
     * @return array
     */
    public static function detectUserClientProvider()
    {
        return array(
            'notSetPatterns(MacOSX)' => array(
                'userAgent' => 'Mac_OS_X/10.9.5 (13F1712) CalendarAgent/176.2',
                'patterns' => null,
                'expected' => array(
                    'platformName' => 'Mac OS X',
                    'platformVersion' => '10.9.5',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '176.2',
                ),
            ),
            'notSetPatterns(iOS)' => array(
                'userAgent' => 'iOS/9.3.1 (13E238) accountsd/1.0',
                'patterns' => null,
                'expected' => array(
                    'platformName' => 'iOS',
                    'platformVersion' => '9.3.1',
                    'clientName' => 'NativeCalendarApplication',
                    'clientVersion' => '1.0',
                ),
            ),
            'emptyPattern' => array(
                'userAgent' => 'iOS/9.3.1 (13E238) accountsd/1.0',
                'patterns' => array(),
                'expected' => null,
            ),
            'predefinedOsName' => array(
                'userAgent' => 'Microsoft Office/16.0 (Windows NT 10.0; Microsoft Outlook 16.0.6326; Pro)',
                'patterns' => array(
                    '#(Windows NT)\s(10).+(Microsoft Outlook)\s([\d.]+)#i' => array('Windows', 2, 3, 4),
                ),
                'expected' => array(
                    'platformName' => 'Windows',
                    'platformVersion' => '10',
                    'clientName' => 'Microsoft Outlook',
                    'clientVersion' => '16.0.6326',
                ),
            ),
            'predefinedOsNameAndOsVersion' => array(
                'userAgent' => 'Microsoft Office/16.0 (Windows NT 10.0; Microsoft Outlook 16.0.6326; Pro)',
                'patterns' => array(
                    '#(Windows NT)\s(10).+(Microsoft Outlook)\s([\d.]+)#i' => array('Windows', '10.3', 3, 4),
                ),
                'expected' => array(
                    'platformName' => 'Windows',
                    'platformVersion' => '10.3',
                    'clientName' => 'Microsoft Outlook',
                    'clientVersion' => '16.0.6326',
                ),
            ),
            'predefinedOsNameAndOsVersionAndClientName' => array(
                'userAgent' =>
                    'Mozilla/5.0 (X11; Linux x86_64; rv:38.0) Gecko/20100101 Thunderbird/38.6.0 Lightning/4.0.5.2',
                'patterns' => array(
                    '#(Linux x86_64).+rv:([\d.]+).+(Thunderbird)/([\d.]+) (Lightning)#i' =>
                        array('Linux', '38', 'Thunderbird/Lightning', 4),
                ),
                'expected' => array(
                    'platformName' => 'Linux',
                    'platformVersion' => '38',
                    'clientName' => 'Thunderbird/Lightning',
                    'clientVersion' => '38.6.0',
                ),
            ),
            'predefinedOsNameAndClientName' => array(
                'userAgent' => 'Mac_OS_X/10.9.5 (13F1712) CalendarAgent/176.2',
                'patterns' => array(
                    '#(mac[\s_+]os[\s_+]x)/([\d.]+).+(calendaragent)/([\d.]+)#i' =>
                        array('MacOS', 2, 'NativeCalendarAgent', 4),
                ),
                'expected' => array(
                    'platformName' => 'MacOS',
                    'platformVersion' => '10.9.5',
                    'clientName' => 'NativeCalendarAgent',
                    'clientVersion' => '176.2',
                ),
            ),
        );
    }

    /**
     * Test detect client by user-agent.
     *
     * @dataProvider Sugarcrm\SugarcrmTests\Dav\Cal\Agent\ClientTest::detectUserClientProvider
     * @covers Sugarcrm\Sugarcrm\Dav\Cal\Agent\Client::parse
     * @param string $userAgent
     * @param array|null $patterns
     * @param array $expected
     */
    public function testDetectUserClient($userAgent, $patterns, $expected)
    {
        $client = new Client();
        if ($patterns !== null) {
            $client->setParsePatterns($patterns);
        }
        $this->assertEquals($expected, $client->parse($userAgent));
    }
}

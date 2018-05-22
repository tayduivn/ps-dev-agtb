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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \MarketingExtrasApi
 */
class MarketingExtrasApiTest extends TestCase
{
    private static $expectedContentUrl = 'https://www.example.com/exciting-content';
    private static $frenchContentUrl = 'https://www.example.com/exciting-content-in-french';

    /**
     * @covers ::getMarketingExtras()
     * @dataProvider providerGetMarketingExtras
     */
    public function testGetMarketingExtras(
        bool $marketingExtrasEnabled,
        string $marketingUrl,
        ?string $selectedLang,
        bool $exception
    ) {
        $api = $this->getMarketingExtrasApiMock(
            array(
                'getMarketingExtrasService',
                'parseArgs',
            )
        );
        $marketingExtras = $this->getMarketingExtrasMock(
            array(
                'areMarketingExtrasEnabled',
                'getMarketingContentUrl',
            )
        );
        $api->method('getMarketingExtrasService')
            ->willReturn($marketingExtras);

        $marketingExtras->expects($this->once())
            ->method('areMarketingExtrasEnabled')
            ->willReturn($marketingExtrasEnabled);

        if ($marketingExtrasEnabled) {
            $getMarketingUrl = $marketingExtras->expects($this->once())
                ->method('getMarketingContentUrl')
                ->with($this->equalTo($selectedLang));

            $api->expects($this->once())
                ->method('parseArgs')
                ->willReturn(array('language' => $selectedLang));

            if ($exception) {
                $getMarketingUrl->will($this->throwException(new \Exception()));
            } else {
                $getMarketingUrl->willReturn($marketingUrl);
            }
        }

        $marketingContent = $api->getMarketingExtras($this->getRestServiceMock(), array($selectedLang));

        $this->assertEquals(array('content_url' => $marketingUrl), $marketingContent);
    }

    public function providerGetMarketingExtras()
    {
        return array(
            // marketing extras on, normal result URL, default language, no exception
            array(
                true,
                MarketingExtrasApiTest::$expectedContentUrl,
                null,
                false,
            ),

            // marketing extras on, French result URL, French language, no exception
            array(
                true,
                MarketingExtrasApiTest::$frenchContentUrl,
                'fr_FR',
                false,
            ),

            // marketing extras off, empty results URL, language doesn't matter, no exception
            array(
                false,
                '',
                null,
                false,
            ),

            // marketing extras on, but somewhere an exception is thrown
            array(
                true,
                '',
                null,
                true,
            ),
        );
    }

    protected function getMarketingExtrasApiMock($methods = null)
    {
        return $this->getMockBuilder('MarketingExtrasApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMarketingExtrasMock($methods = null)
    {
        return $this->getMockBuilder('\Sugarcrm\Sugarcrm\Marketing\MarketingExtras')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getRestServiceMock($methods = null)
    {
        return $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}

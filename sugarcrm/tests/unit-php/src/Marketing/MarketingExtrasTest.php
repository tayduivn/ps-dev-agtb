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

namespace Sugarcrm\SugarcrmTestsUnit\Marketing;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Marketing\MarketingExtras
 */
class MarketingExtrasTest extends TestCase
{
    private static $expectedContentUrl = 'https://www.example.com/exciting-content';

    /**
     * @covers ::getMarketingContentUrl
     */
    public function testGetMarketingContentUrl()
    {
        $marketingExtras = $this->getMarketingExtrasMock(
            [
                'areMarketingExtrasEnabled',
            ]
        );

        $oldBuild = $GLOBALS['sugar_build'] ?? null;
        $oldFlavor = $GLOBALS['sugar_flavor'] ?? null;
        $oldVersion = $GLOBALS['sugar_version'] ?? null;
        $GLOBALS['sugar_build'] = 55555;
        $GLOBALS['sugar_flavor'] = 'pro';
        $GLOBALS['sugar_version'] = '8.1.0';

        $marketingExtras->expects($this->once())
            ->method('areMarketingExtrasEnabled')
            ->willReturn(false);

        $contentUrl = $marketingExtras->getMarketingContentUrl('en_us');

        $this->assertEquals('', $contentUrl);

        $GLOBALS['sugar_build'] = $oldBuild;
        $GLOBALS['sugar_flavor'] = $oldFlavor;
        $GLOBALS['sugar_version'] = $oldVersion;
    }

    /**
     * Test xss fix.
     * @covers ::getMarketingContentUrl
     * @dataProvider providerGetMarketingContentUrlXss
     */
    public function testGetMarketingContentUrlXss($contentUrl, $expected)
    {
        if ($expected === 'exception') {
            $this->expectException(\Exception::class);
        }
        $marketingExtras = $this->getMarketingExtrasMock(
            [
                'areMarketingExtrasEnabled',
                'getMarketingExtrasUrl',
                'fetchMarketingContentInfo',
                'getSugarDetails',
            ]
        );
        $marketingExtras->expects($this->once())
            ->method('areMarketingExtrasEnabled')
            ->willReturn(true);
        $marketingExtras->expects($this->once())
            ->method('getMarketingExtrasUrl')
            ->willReturn('http://example.com/url');
        $marketingExtras->expects($this->once())
            ->method('fetchMarketingContentInfo')
            ->willReturn(['content_url' => $contentUrl]);
        $marketingExtras->expects($this->once())
            ->method('getSugarDetails')
            ->willReturn(['version' => '9.2.0', 'flavor' => 'ent', 'build' => '777']);
        $this->assertEquals($expected, $marketingExtras->getMarketingContentUrl('en_Us'));
    }

    public function providerGetMarketingContentUrlXss()
    {
        return [
            [
                'http://example.com/content',
                'http://example.com/content',
            ],
            [
                'javascript:alert(\'test: \' + window.parent.App.lang.direction)',
                'exception',
            ],
        ];
    }

    /**
     * @covers ::getBackgroundImageUrl
     * @dataProvider providerGetBackgroundImageUrl
     */
    public function testGetBackgroundImageUrl($image, $default, $expected)
    {
        if ($expected === 'exception') {
            $this->expectException(\Exception::class);
        }
        $marketingExtras = $this->getMarketingExtrasMock(
            [
                'getSugarConfig',
            ]
        );
        $map = [
            ['background_image', null, $image],
            ['default_background_image', null, $default],
            ['site_url', null, 'http://mysugar.com'],
        ];
        $marketingExtras->method('getSugarConfig')
            ->will($this->returnValueMap($map));
        $this->assertEquals($expected, $marketingExtras->getBackgroundImageUrl());
    }

    public function providerGetBackgroundImageUrl()
    {
        return [
            [
                'http://wwww.example.com/image.jpg',
                'include/images/coffeeCup-sugar-sm.png',
                'http://wwww.example.com/image.jpg',
            ],
            [
                'ht//wwww.example.com/image.jpg',
                'include/images/coffeeCup-sugar-sm.png',
                'http://mysugar.com/include/images/coffeeCup-sugar-sm.png',
            ],
            [
                'ht//wwww.example.com/image.jpg',
                'include/images/nothing.jpg',
                'exception',
            ],
            [
                'ht//wwww.example.com/image.jpg',
                '/tmp/nothing.jpg',
                'exception',
            ],
            [
                'ht//wwww.example.com/image.jpg',
                'include/images/../../../bad.jpg',
                'exception',
            ],
        ];
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

    protected function getMockValidator($methods = null)
    {
        return $this->getMockBuilder('ValidatorInterface')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMockConstraints()
    {
        return $this->getMockBuilder('Constraint')
            ->disableOriginalConstructor()
            ->getMock();
    }
}

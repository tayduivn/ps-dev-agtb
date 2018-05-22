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
            array(
                'areMarketingExtrasEnabled',
            )
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

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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarOAuth2;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarOAuth2Server
 */
class SugarOAuth2ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provides data for testGetOAuth2Server
     * @return array
     */
    public function getOAuth2ServerProvider()
    {
        return [
            'oldOAuthServer' => [
                'oidcEnabled' => false,
                'expectedServerClass' => \SugarOAuth2Server::class,
                'expectedStorageClass' => \SugarOAuth2Storage::class,
            ],
            'oidcOAuthServer' => [
                'oidcEnabled' => true,
                'expectedServerClass' => \SugarOAuth2ServerOIDC::class,
                'expectedStorageClass' => \SugarOAuth2StorageOIDC::class,
            ],
        ];
    }

    /**
     * @param $platformType
     * @param $expectedServerClass
     * @param $expectedStorageClass
     *
     * @dataProvider getOAuth2ServerProvider
     * @covers ::getOAuth2Server
     */
    public function testGetOAuth2Server($platformType, $expectedServerClass, $expectedStorageClass)
    {
        $oAuthServer = SugarOAuth2ServerMock::getOAuth2Server($platformType);
        $this->assertInstanceOf($expectedServerClass, $oAuthServer);
        $this->assertInstanceOf(
            $expectedStorageClass,
            TestReflection::getProtectedValue($oAuthServer, 'storage')
        );
    }
}

/**
 * Mock for SugarOAuth2Server to prevent caching
 */
class SugarOAuth2ServerMock extends \SugarOAuth2Server
{
    /**
     * @param bool $oidcEnabled
     * @return \SugarOAuth2Server
     */
    public static function getOAuth2Server($oidcEnabled = false)
    {
        parent::$currentOAuth2Server = null;
        return parent::getOAuth2Server($oidcEnabled);
    }
}

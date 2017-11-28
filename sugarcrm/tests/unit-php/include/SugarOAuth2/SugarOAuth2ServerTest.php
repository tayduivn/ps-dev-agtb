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
     * @var \SugarConfig
     */
    protected $sugarConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->sugarConfig = \SugarConfig::getInstance();
    }

    protected function tearDown()
    {
        $this->sugarConfig->_cached_values = [];
    }
    /**
     * Provides data for testGetOAuth2Server
     * @return array
     */
    public function getOAuth2ServerProvider()
    {
        return [
            'oldOAuthServer' => [
                'oidcOauth' => [],
                'expectedServerClass' => \SugarOAuth2Server::class,
                'expectedStorageClass' => \SugarOAuth2Storage::class,
            ],
            'oidcOAuthServer' => [
                'oidcOauth' => [
                    'clientId' => 'testLocal',
                    'clientSecret' => 'testLocalSecret',
                    'oidcUrl' => 'http://localhost:4444',
                    'idpUrl' => 'http://sugar.dolbik.dev/idm205idp/web/',
                    'oidcKeySetId' => 'testkey2',
                ],
                'expectedServerClass' => \SugarOAuth2ServerOIDC::class,
                'expectedStorageClass' => \SugarOAuth2StorageOIDC::class,
            ],
        ];
    }

    /**
     * @param array $oidcOauth
     * @param $expectedServerClass
     * @param $expectedStorageClass
     *
     * @dataProvider getOAuth2ServerProvider
     * @covers ::getOAuth2Server
     */
    public function testGetOAuth2Server(array $oidcOauth, $expectedServerClass, $expectedStorageClass)
    {
        $this->sugarConfig->_cached_values['oidc_oauth'] = $oidcOauth;
        $oAuthServer = SugarOAuth2ServerMock::getOAuth2Server();
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
     * @param string $platform
     * @return \SugarOAuth2Server
     */
    public static function getOAuth2Server($platform = null)
    {
        parent::$currentOAuth2Server = null;
        return parent::getOAuth2Server();
    }
}

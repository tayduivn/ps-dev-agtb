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

namespace Sugarcrm\SugarcrmTestUnit\modules\Users\authentication\OAuth2Authenticate;

use SugarConfig;
use PHPUnit_Framework_TestCase as TestCase;
use OAuth2Authenticate;

/**
 * @coversDefaultClass \OAuth2Authenticate
 */
class OAuth2AuthenticateTest extends TestCase
{
    /**
     * @var OAuth2Authenticate
     */
    protected $auth;

    /**
     * @var array|mixed
     */
    protected $savedConfig = [];

    /**
     * set up
     */
    protected function setUp()
    {
        $this->savedConfig['site_url'] = SugarConfig::getInstance()->get('site_url');
        $this->savedConfig['oidc_oauth'] = SugarConfig::getInstance()->get('oidc_oauth');
        SugarConfig::getInstance()->_cached_values['site_url'] = 'http://test.sugarcrm.local';
        $this->auth = new OAuth2Authenticate();
    }

    /**
     * tear down
     */
    protected function tearDown()
    {
        SugarConfig::getInstance()->_cached_values['site_url'] = $this->savedConfig['site_url'];
        SugarConfig::getInstance()->_cached_values['oidc_oauth'] = $this->savedConfig['oidc_oauth'];
    }

    /**
     * @covers ::getLoginUrl
     */
    public function testGetLoginUrlWithValidConfig()
    {
        SugarConfig::getInstance()->_cached_values['oidc_oauth'] = [
            'clientId' => 'testLocal',
            'clientSecret' => 'testLocalSecret',
            'redirectUri' => '',
            'oidcUrl' => 'http://sts.sugarcrm.local',
            'idpUrl' => 'http://idp.url',
            'oidcKeySetId' => 'keySetId',
        ];
        $this->assertEquals('http://sts.sugarcrm.local', $this->auth->getLoginUrl());
    }

    /**
     * @covers ::getLoginUrl
     *
     * @expectedException \RuntimeException
     */
    public function testGetLoginUrlWithEmptyConfig()
    {
        SugarConfig::getInstance()->_cached_values['oidc_oauth'] = null;
        $this->auth->getLoginUrl();
    }

    /**
     * @covers ::getLogoutUrl
     */
    public function testGetLogoutUrl()
    {
        $this->assertFalse($this->auth->getLogoutUrl());
    }

    /**
     * @covers ::loginAuthenticate
     */
    public function testLoginAuthenticate()
    {
        $this->assertFalse($this->auth->loginAuthenticate('testUser', 'testPassword'));
    }
}

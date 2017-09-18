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
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * @coversDefaultClass OAuth2Authenticate
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
        SugarConfig::getInstance()->_cached_values['oidc_oauth'] = [
             'clientId' => 'testLocal',
             'clientSecret' => 'testLocalSecret',
             'redirectUri' => '',
             'oidcUrl' => 'http://sts.sugarcrm.local',
        ];
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
     * @covers ::__construct
     * @covers ::getLoginUrl
     * @covers ::getLogoutUrl
     * @covers ::getOidcUrl
     */
    public function testConstructAndGetters()
    {
        $this->assertContains('client_id=testLocal', $this->auth->getLoginUrl());
        $this->assertFalse($this->auth->getLogoutUrl());
        $this->assertEquals('http://sts.sugarcrm.local', $this->auth->getOidcUrl());
    }

    /**
     * @covers ::getAccessToken
     */
    public function testGetAccessToken()
    {
        $code = 'test';
        $token = new AccessToken(['access_token' => 'token']);
        $provider = $this->getMockBuilder(GenericProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccessToken'])
            ->getMock();
        $provider->expects($this->once())
            ->method('getAccessToken')
            ->with($this->equalTo('authorization_code'), $this->equalTo(['code' => $code]))
            ->willReturn($token);

        $this->auth->setOAuthProvider($provider);
        $this->auth->getAccessToken($code);
    }
    /**
     * @covers ::introspectAccessToken
     */
    public function testIntrospectAccessToken()
    {
        $code = 'test';

        $provider = $this->getMockBuilder(GenericProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['introspectToken'])
            ->getMock();
        $provider->expects($this->once())
            ->method('introspectToken')
            ->with($this->callback(function (AccessToken $token) {
                $this->assertEquals('test', $token->getToken());
                return true;
            }))
            ->willReturn(['sub' => 'max']);

        $this->auth->setOAuthProvider($provider);
        $this->auth->introspectAccessToken($code);
    }
}

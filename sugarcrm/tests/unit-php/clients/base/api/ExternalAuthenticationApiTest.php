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

require_once 'include/utils.php';

/**
 * Class ExternalAuthenticationApiTest
 * @coversDefaultClass \ExternalAuthenticationApi
 */
class ExternalAuthenticationApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ServiceBase | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $service = null;

    /**
     * @var \ExternalAuthenticationApi | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $api = null;

    /**
     * @var \AuthenticationController | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $auth = null;

    /**
     * @var \SugarConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config = null;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->service = $this->createMock(\ServiceBase::class);
        $this->auth = $this->createMock(\AuthenticationController::class);
        $this->config = $this->createMock(\SugarConfig::class);
        $this->api = $this->getMockBuilder(\ExternalAuthenticationApi::class)
                          ->setMethods(['getSugarConfig', 'getAuthenticationController'])
                          ->getMock();
        $this->api->method('getSugarConfig')->willReturn($this->config);
        $this->api->method('getAuthenticationController')->willReturn($this->auth);
    }

    /**
     * Checks logic when required arguments are missing.
     *
     * @covers ::getLoginUrl
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testGetLoginUrlWithInvalidArs()
    {
        $args = [];
        $this->api->getLoginUrl($this->service, $args);
    }

    /**
     * Checks logic when authentication is not external
     *
     * @covers ::getLoginUrl
     * @expectedException \SugarApiExceptionError
     */
    public function testGetLoginUrlNotExternalAuth()
    {
        $this->config->expects($this->exactly(2))
                     ->method('get')
                     ->with('authenticationClass')
                     ->willReturn('SugarAuthenticate');
        $args = ['platform' => 'base'];
        $this->auth->method('isExternal')->willReturn(false);
        $this->api->getLoginUrl($this->service, $args);
    }

    /**
     * Checks logic when authentication class not present in config
     *
     * @covers ::getLoginUrl
     * @expectedException \SugarApiExceptionError
     */
    public function testGetLoginUrlNoAuth()
    {
        $args = ['platform' => 'base'];
        $this->config->expects($this->exactly(1))->method('get')->with('authenticationClass')->willReturn(null);
        $this->api->getLoginUrl($this->service, $args);
    }

    /**
     * Checks logic when authentication is present.
     *
     * @covers ::getLoginUrl
     */
    public function testGetLoginUrl()
    {
        $this->config->expects($this->exactly(2))
                     ->method('get')
                     ->with('authenticationClass')
                     ->willReturn('SAMLAuthenticate');
        $args = ['platform' => 'base'];
        $this->auth->method('isExternal')->willReturn(true);
        $this->auth->expects($this->once())
                   ->method('getLoginUrl')
                   ->with(['platform' => 'base'])
                   ->willReturn('http://test.com');
        $result = $this->api->getLoginUrl($this->service, $args);
        $this->assertEquals(['url' => 'http://test.com'], $result);
    }
}

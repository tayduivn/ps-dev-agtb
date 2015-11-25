<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\clients\base\api;

/**
 * Class TokenVerificationApiTest
 * @package Sugarcrm\SugarcrmTests\clients\base\api
 * @coversDefaultClass \TokenVerificationApi
 */
class TokenVerificationApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\TokenVerificationApi
     */
    protected $tokenVerificationApi;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Administration
     */
    protected $administration;

    /**
     * @var \RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        parent::setUp();
        $this->tokenVerificationApi = $this->getMock('TokenVerificationApi', array('getAdministrationBean'));
        $this->administration = $this->getMock(
            'Administration',
            array('getConfigForModule', 'saveSetting')
        );
        $this->serviceMock = \SugarTestRestUtilities::getRestServiceMock();
    }

    /**
     * @param array $args
     * @dataProvider providerVerifyTokenThrowsWithoutRequiredArgs
     * @covers ::verifyToken
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testVerifyTokenThrowsWithoutRequiredArgs($args)
    {
        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public function providerVerifyTokenThrowsWithoutRequiredArgs()
    {
        return array(
            'throws if "id" isn\'t presented' => array(
                array()
            ),
            'throws if "original" isn\'t presented' => array(
                array('id' => 'dummy-external-valid-token-id')
            ),
            'throws if "verified" isn\'t presented' => array(
                array('id' => 'dummy-external-valid-token-id', 'original' => 'dummy-original-token')
            )
        );
    }

    /**
     * @covers ::verifyToken
     * @expectedException \SugarApiExceptionInvalidParameter
     */
    public function testVerifyTokenThrowsWhenIdNotInAllowedList()
    {
        $args = array(
            'id' => 'dummy-external-valid-token-id',
            'original' => 'dummy-original-token',
            'verified' => 'dummy-verified-token'
        );
        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);

    }

    /**
     * @covers ::verifyToken
     * @expectedException \SugarApiExceptionEditConflict
     */
    public function testVerifyTokenThrowsWhenOriginalAndStoredTokensAreNotEqual()
    {
        $args = array(
            'id' => 'socket',
            'original' => 'dummy-original-token',
            'verified' => 'dummy-verified-token'
        );

        $this->administration->method('getConfigForModule')
            ->willReturn(array('external_token_socket' => 'dummy-token'));

        $this->tokenVerificationApi->method('getAdministrationBean')->willReturn($this->administration);

        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * @param array $args
     * @param array $expected
     * @dataProvider providerVerifyTokenCallsSaveSettingWithCorrectArguments
     * @covers ::verifyToken
     */
    public function testVerifyTokenCallsSaveSettingWithCorrectArguments($args, $expected)
    {
        $this->administration->method('getConfigForModule')
            ->willReturn(array(
                'external_token_socket' => 'dummy-original-token',
                'external_token_trigger' => 'dummy-original-token'
            ));

        $this->administration->expects($this->once())
            ->method('saveSetting')
            ->with($expected[0], $expected[1], $expected[2], $expected[3]);

        $this->tokenVerificationApi->method('getAdministrationBean')->willReturn($this->administration);

        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public function providerVerifyTokenCallsSaveSettingWithCorrectArguments()
    {
        return array(
            'id is "socket"' => array(
                array(
                    'id' => 'socket',
                    'original' => 'dummy-original-token',
                    'verified' => 'dummy-verified-socket-token'
                ),
                array(
                    'auth',
                    'external_token_socket',
                    'dummy-verified-socket-token',
                    'base'
                )
            ),
            'id is "trigger"' => array(
                array(
                    'id' => 'trigger',
                    'original' => 'dummy-original-token',
                    'verified' => 'dummy-verified-trigger-token'
                ),
                array(
                    'auth',
                    'external_token_trigger',
                    'dummy-verified-trigger-token',
                    'base'
                )
            ),
        );
    }
}

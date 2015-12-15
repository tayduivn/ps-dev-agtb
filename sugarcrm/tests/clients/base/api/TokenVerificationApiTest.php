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

use TokenVerificationApi;

/**
 * Class TokenVerificationApiTest
 * @package Sugarcrm\SugarcrmTests\clients\base\api
 * @coversDefaultClass TokenVerificationApi
 */
class TokenVerificationApiTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var TokenVerificationApi */
    protected $tokenVerificationApi;

    /**
     * @var \RestService
     */
    protected $serviceMock;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->tokenVerificationApi = new TokenVerificationApi();
        $this->serviceMock = \SugarTestRestUtilities::getRestServiceMock();
        \BeanFactory::setBeanClass('Administration', 'Sugarcrm\SugarcrmTests\clients\base\api\AdministrationCRYS1259');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        AdministrationCRYS1259::$testData = array();
        \BeanFactory::setBeanClass('Administration');
        parent::tearDown();
    }

    /**
     * verifyToken method should throw on missed parameter
     *
     * @param array $args
     * @dataProvider providerVerifyTokenThrowsWithoutRequiredArgs
     * @covers TokenVerificationApi::verifyToken
     * @expectedException \SugarApiExceptionMissingParameter
     */
    public function testVerifyTokenThrowsWithoutRequiredArgs($args)
    {
        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * @return array
     */
    public static function providerVerifyTokenThrowsWithoutRequiredArgs()
    {
        return array(
            'throws if "id" isn\'t presented' => array(
                array(),
            ),
            'throws if "original" isn\'t presented' => array(
                array(
                    'id' => 'dummy-external-valid-token-id',
                ),
            ),
            'throws if "verified" isn\'t presented' => array(
                array(
                    'id' => 'dummy-external-valid-token-id',
                    'original' => 'dummy-original-token',
                ),
            ),
        );
    }

    /**
     * verifyToken method should throw if id is not valid
     *
     * @covers TokenVerificationApi::verifyToken
     * @expectedException \SugarApiExceptionInvalidParameter
     */
    public function testVerifyTokenThrowsWhenIdNotInAllowedList()
    {
        $args = array(
            'id' => 'dummy-external-valid-token-id',
            'original' => 'dummy-original-token',
            'verified' => 'dummy-verified-token',
        );
        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * verifyToken method should throw if original token is not valid
     *
     * @covers TokenVerificationApi::verifyToken
     * @expectedException \SugarApiExceptionEditConflict
     */
    public function testVerifyTokenThrowsWhenOriginalAndStoredTokensAreNotEqual()
    {
        $args = array(
            'id' => 'socket',
            'original' => rand(1000, 9999),
            'verified' => 'dummy-verified-token'
        );

        AdministrationCRYS1259::$testData['auth']['base']['external_token_socket'] = $args['original'] + 1;

        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
    }

    /**
     * @param array $args
     * @dataProvider providerVerifyTokenCallsSaveSettingWithCorrectArguments
     * @covers TokenVerificationApi::verifyToken
     */
    public function testVerifyTokenCallsSaveSettingWithCorrectArguments($args)
    {
        AdministrationCRYS1259::$testData['auth']['base']['external_token_' . $args['id']] = $args['original'];
        $this->tokenVerificationApi->verifyToken($this->serviceMock, $args);
        $this->assertEquals($args['verified'], AdministrationCRYS1259::$testData['auth']['base']['external_token_' . $args['id']]);
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
                    'verified' => 'dummy-verified-socket-token',
                ),
            ),
            'id is "trigger"' => array(
                array(
                    'id' => 'trigger',
                    'original' => 'dummy-original-token',
                    'verified' => 'dummy-verified-trigger-token',
                ),
            ),
        );
    }
}

/**
 * Sub class for Administration bean
 *
 * Class AdministrationCRYS1259
 * @package Sugarcrm\SugarcrmTests\clients\base\api
 */
class AdministrationCRYS1259 extends \Administration
{
    /** @var array */
    public static $testData = array();

    /**
     * @inheritDoc
     */
    public function getConfigForModule($module, $platform = 'base', $clean = false)
    {
        if (isset(static::$testData[$module][$platform])) {
            return static::$testData[$module][$platform];
        }
        return array();
    }

    /**
     * @inheritDoc
     */
    public function saveSetting($category, $key, $value, $platform = '')
    {
        static::$testData[$category][$platform][$key] = $value;
    }
}

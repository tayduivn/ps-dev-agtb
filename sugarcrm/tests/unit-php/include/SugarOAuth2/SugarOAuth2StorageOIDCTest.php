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

use SugarOAuth2StorageOIDC;
use AuthenticationController;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

require_once 'include/utils.php';
require_once 'include/SugarCache/SugarCache.php';

/**
 * @coversDefaultClass  \SugarOAuth2StorageOIDC
 */
class SugarOAuth2StorageOIDCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SugarOAuth2StorageOIDC
     */
    protected $storageMock;

    /**
     * @var AuthenticationController
     */
    protected $authController;

    protected function setUp()
    {
        $this->authController = $this->createMock(AuthenticationController::class);

        $this->storageMock = $this->getMockBuilder(SugarOAuth2StorageOIDC::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAuthController'])
            ->getMock();

        $this->storageMock->method('getAuthController')->willReturn($this->authController);

        $GLOBALS['log'] = $this->getMockBuilder(\LoggerManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($GLOBALS['log']);
    }

    public function providerCheckUserCredentialsFailedLogin()
    {
        return [
            [false],
            [[]],
        ];
    }

    /**
     * @covers ::checkUserCredentials
     *
     * @dataProvider providerCheckUserCredentialsFailedLogin
     * @param mixed $loginResult
     *
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testCheckUserCredentialsFailedLogin($loginResult)
    {
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willReturn($loginResult);
        $this->storageMock->checkUserCredentials('sugar', 'user', 'password');
    }

    /**
     * @covers ::checkUserCredentials
     */
    public function testCheckUserCredentialsSuccessfulLogin()
    {
        $loginResult = [
            'user_id' => '123',
            'scope' => null,
        ];
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willReturn($loginResult);
        $this->assertEquals($loginResult, $this->storageMock->checkUserCredentials('sugar', 'user', 'password'));
    }

    /**
     * @covers ::checkUserCredentials
     *
     * @expectedException \SugarApiExceptionNeedLogin
     */
    public function testCheckUserCredentialsLoginException()
    {
        $this->authController->method('login')
            ->with(
                'user',
                'password',
                ['passwordEncrypted' => false, 'noRedirect' => true, 'noHooks' => true]
            )
            ->willThrowException(new AuthenticationException());
        $this->storageMock->checkUserCredentials('sugar', 'user', 'password');
    }
}

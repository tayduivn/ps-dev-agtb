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

namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider\Authentication\Listener\Success;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDCSessionListener;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

require_once 'include/utils/security_utils.php';

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener\Success\OIDCSessionListener
 */
class OIDCSessionListenerTest extends TestCase
{
    /**
     * @var \User|MockObject
     */
    protected $sugarUser;

    /**
     * @var OIDCSessionListener
     */
    protected $listener;

    /**
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * @var User|MockObject
     */
    protected $user;

    /**
     * @var IntrospectToken|MockObject
     */
    protected $token;

    /**
     * @var \SugarConfig|MockObject
     */
    protected $sugarConfig;

    /**
     * @var string
     */
    protected $accessToken = null;

    /**
     * @var array | null
     */
    protected $currentSession = null;

    /**
     * @var array | null
     */
    protected $currentServer = null;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->accessToken = 'test_' . time();

        $this->sugarUser = $this->createMock(\User::class);

        $this->user = $this->createMock(User::class);
        $this->user->method('getSugarUser')->willReturn($this->sugarUser);

        $this->token = $this->getMockBuilder(IntrospectToken::class)
            ->setConstructorArgs(
                [
                    $this->accessToken,
                    'srn:cloud:idp:eu:0000000001:tenant',
                    'https://apis.sugarcrm.com/auth/crm',
                ]
            )
            ->setMethods(['getUser', 'getAttribute'])
            ->getMock();
        $this->token->method('getUser')->willReturn($this->user);

        $this->sugarConfig = $this->createMock(\SugarConfig::class);

        $this->event = new AuthenticationEvent($this->token);
        $this->listener = $this->getMockBuilder(OIDCSessionListener::class)
                               ->setMethods(['getSugarConfig'])
                               ->getMock();
        $this->listener->method('getSugarConfig')->willReturn($this->sugarConfig);

        if (isset($_SESSION)) {
            $this->currentSession = $_SESSION;
        }

        $this->currentServer = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $_SESSION = $this->currentSession;
        $_SERVER = $this->currentServer;
    }

    /**
     * @runInSeparateProcess
     * @covers ::execute
     */
    public function testExecuteWithNewSession()
    {
        $this->sugarConfig->expects($this->once())
                          ->method('get')
                          ->with('unique_key')
                          ->willReturn('unique_key');

        $this->token->expects($this->once())
                    ->method('getAttribute')
                    ->with('platform')
                    ->willReturn('opi');

        $this->sugarUser->id = 1;

        $this->listener->execute($this->event);

        $this->assertEquals(hash('sha256', $this->accessToken), session_id());
        $this->assertTrue($_SESSION['externalLogin']);
        $this->assertTrue($_SESSION['is_valid_session']);
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('127.0.0.2', $_SESSION['ip_address']);
        $this->assertEquals('user', $_SESSION['type']);
        $this->assertEquals(1, $_SESSION['authenticated_user_id']);
        $this->assertEquals('unique_key', $_SESSION['unique_key']);
        $this->assertEquals('opi', $_SESSION['platform']);
    }

    /**
     * @runInSeparateProcess
     * @covers ::execute
     */
    public function testExecuteWithExistingSession()
    {
        $this->sugarConfig->expects($this->never())->method('get');
        $this->token->expects($this->never())->method('getAttribute');

        ini_set("session.use_cookies", false);
        session_id(hash('sha256', $this->accessToken));
        session_start();

        $_SESSION['externalLogin'] = true;
        $_SESSION['is_valid_session'] = true;
        $_SESSION['ip_address'] = '127.0.0.3';
        $_SESSION['user_id'] = 2;
        $_SESSION['type'] = 'user';
        $_SESSION['authenticated_user_id'] = 2;
        $_SESSION['unique_key'] = 'ukey';
        $_SESSION['platform'] = 'base';

        $this->listener->execute($this->event);

        $this->assertEquals(hash('sha256', $this->accessToken), session_id());
        $this->assertTrue($_SESSION['externalLogin']);
        $this->assertTrue($_SESSION['is_valid_session']);
        $this->assertEquals(2, $_SESSION['user_id']);
        $this->assertEquals('127.0.0.3', $_SESSION['ip_address']);
        $this->assertEquals('user', $_SESSION['type']);
        $this->assertEquals(2, $_SESSION['authenticated_user_id']);
        $this->assertEquals('ukey', $_SESSION['unique_key']);
        $this->assertEquals('base', $_SESSION['platform']);
    }
}

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

namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication\Provider;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider
 */
class OIDCAuthenticationProviderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var OIDCAuthenticationProvider
     */
    protected $provider = null;

    /**
     * @var UserProviderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userProvider = null;

    /**
     * @var UserCheckerInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $userChecker = null;

    /**
     * @var GenericProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $oAuthProvider = null;

    protected $user = null;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->userProvider = $this->createMock(UserProviderInterface::class);
        $this->oAuthProvider = $this->createMock(GenericProvider::class);
        $this->user = new User();
        $this->provider =new OIDCAuthenticationProvider(
            $this->oAuthProvider,
            $this->userProvider,
            $this->userChecker
        );
    }

    /**
     * Checks supports logic.
     *
     * @covers ::supports
     */
    public function testSupportsWithSupportedToken()
    {
        $token = new IntrospectToken('test');
        $this->assertTrue($this->provider->supports($token));
    }

    /**
     * Checks supports logic.
     *
     * @covers ::supports
     */
    public function testSupportsWithUnsupportedToken()
    {
        $token = new UsernamePasswordToken('test', 'test', 'test');
        $this->assertFalse($this->provider->supports($token));
    }

    /**
     * @covers ::authenticate
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationServiceException
     */
    public function testAuthenticateWithUnsupportedToken()
    {
        $token = new UsernamePasswordToken('test', 'test', 'test');
        $this->provider->authenticate($token);
    }

    /**
     * Provides data for testAuthenticateWithInvalidToken
     * @return array
     */
    public function authenticateWithInvalidTokenProvider()
    {
        return [
            'emptyResult' => [
                [],
            ],
            'inactiveResult' => [
                ['active' => false],
            ],
        ];
    }

    /**
     * @param $tokenResult
     *
     * @covers ::authenticate
     * @dataProvider authenticateWithInvalidTokenProvider
     *
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testAuthenticateWithInvalidToken($tokenResult)
    {
        $token = new IntrospectToken('token');
        $this->oAuthProvider->expects($this->once())
                            ->method('introspectToken')
                            ->with('token')
                            ->willReturn($tokenResult);
        $this->provider->authenticate($token);
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticate()
    {
        $introspectResult = [
            'active' => true,
            'scope' => 'offline',
            'client_id' => 'testLocal',
            'sub' => 'test@test.com',
            'exp' => 1507571717,
            'iat' => 1507535718,
            'aud' => 'testLocal',
            'iss' => 'http://sts.sugarcrm.local',
            'ext' => ['amr' => ['PROVIDER_KEY_SAML']],
        ];

        $token = new IntrospectToken('token');
        $token->setAttribute('platform', 'opi');
        $this->oAuthProvider->expects($this->once())
                            ->method('introspectToken')
                            ->with('token')
                            ->willReturn($introspectResult);
        $this->userProvider->expects($this->once())
                           ->method('loadUserByUsername')
                           ->with($introspectResult['sub'])
                           ->willReturn($this->user);
        $this->userChecker->expects($this->once())->method('checkPostAuth')->with($this->user);
        $resultToken = $this->provider->authenticate($token);

        $this->assertInstanceOf(IntrospectToken::class, $resultToken);
        $this->assertEquals('opi', $resultToken->getAttribute('platform'));
        $this->assertEquals('token', $resultToken->getCredentials());
        foreach ($introspectResult as $key => $expectedValue) {
            $this->assertEquals($expectedValue, $resultToken->getAttribute($key));
        }
    }
}

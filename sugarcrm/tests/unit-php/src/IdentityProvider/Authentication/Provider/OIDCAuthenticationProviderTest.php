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

use League\OAuth2\Client\Token\AccessToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

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
     * @var SugarOIDCUserProvider | \PHPUnit_Framework_MockObject_MockObject
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
        $this->userProvider = $this->createMock(SugarOIDCUserProvider::class);
        $this->oAuthProvider = $this->createMock(GenericProvider::class);
        $this->user = new User();
        $this->provider =new OIDCAuthenticationProvider(
            $this->oAuthProvider,
            $this->userProvider,
            $this->userChecker
        );
    }

    /**
     * Provides data for testSupportsWithSupportedToken
     *
     * @return array
     */
    public function supportsWithSupportedTokenProvider()
    {
        return [
            'introspectToken' => [
                'tokenClass' => new IntrospectToken('test'),
                ],
            'jwtToken' => [
                'tokenClass' => new JWTBearerToken('testId', 'srn:tenant'),
            ],
        ];
    }

    /**
     * Checks supports logic.
     *
     * @param TokenInterface $token
     *
     * @covers ::supports
     * @dataProvider supportsWithSupportedTokenProvider
     */
    public function testSupportsWithSupportedToken(TokenInterface $token)
    {
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
    public function testAuthenticateWithIntrospectToken()
    {
        $introspectResult = [
            'active' => true,
            'scope' => 'offline',
            'client_id' => 'testLocal',
            'sub' => 'srn:cluster:idm:eu:0000000001:user:seed_sally_id',
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
                           ->method('loadUserBySrn')
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

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateWithJwtBearerToken()
    {
        $keySetInfo = [
            'keys' => [
                'private' => ['kid' => 'private'],
                'public' => ['kid' => 'public'],
            ],
            'keySetId' => 'setId',
            'clientId' => 'testLocal',
        ];

        $accessToken = new AccessToken(
            [
                'access_token' => 'accessToken',
                'expires_in' => 100,

            ]
        );

        $token = $this->getMockBuilder(JWTBearerToken::class)
                      ->enableOriginalConstructor()
                      ->setConstructorArgs(
                          ['srn:cluster:idm:eu:0000000001:user:seed_sally_id', 'srn:cluster:idm:eu:0000000001:tenant']
                      )
                      ->setMethods(['__toString'])
                      ->getMock();
        $token->expects($this->once())->method('__toString')->willReturn('assertion');
        $this->oAuthProvider->expects($this->once())->method('getKeySet')->willReturn($keySetInfo);
        $this->oAuthProvider->expects($this->once())
                            ->method('getBaseAccessTokenUrl')
                            ->willReturn('http://test.url');
        $this->oAuthProvider->expects($this->once())
                            ->method('getJwtBearerAccessToken')
                            ->with('assertion')
                            ->willReturn($accessToken);

        $this->userProvider->expects($this->once())
                           ->method('loadUserByField')
                           ->with('seed_sally_id', 'id')
                           ->willReturn($this->user);

        $result = $this->provider->authenticate($token);

        $this->assertEquals('accessToken', $result->getAttribute('token'));
        $this->assertNotEmpty($result->getAttribute('expires_in'));
        $this->assertNotEmpty($result->getAttribute('exp'));
    }
}

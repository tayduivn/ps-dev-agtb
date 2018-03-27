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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\OAuth2\Client\Provider\IdmProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider\OIDCAuthenticationProvider
 */
class OIDCAuthenticationProviderTest extends TestCase
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
     * @var SugarOIDCUserProvider|MockObject
     */
    protected $userProvider = null;

    /**
     * @var UserCheckerInterface|MockObject
     */
    protected $userChecker = null;

    /**
     * @var IdmProvider|MockObject
     */
    protected $oAuthProvider = null;

    /**
     * @var User\Mapping\SugarOidcUserMapping
     */
    protected $userMapping;

    protected $user = null;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->userChecker = $this->createMock(UserCheckerInterface::class);
        $this->userProvider = $this->createMock(SugarOIDCUserProvider::class);
        $this->oAuthProvider = $this->createMock(IdmProvider::class);
        $this->oAuthProvider->method('getScopeSeparator')->willReturn(' ');
        $this->userMapping = new User\Mapping\SugarOidcUserMapping();
        $this->user = new User();
        $this->provider =new OIDCAuthenticationProvider(
            $this->oAuthProvider,
            $this->userProvider,
            $this->userChecker,
            $this->userMapping
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
                'tokenClass' => new IntrospectToken(
                    'test',
                    'srn:cloud:idp:eu:0000000001:tenant',
                    'https://apis.sugarcrm.com/auth/crm'
                ),
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
        $token = new IntrospectToken(
            'token',
            'srn:cloud:idp:eu:0000000001:tenant',
            'https://apis.sugarcrm.com/auth/crm'
        );
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
            'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
            'client_id' => 'testLocal',
            'sub' => 'srn:cluster:idm:eu:0000000001:user:seed_sally_id',
            'exp' => 1507571717,
            'iat' => 1507535718,
            'aud' => 'testLocal',
            'iss' => 'http://sts.sugarcrm.local',
            'ext' => [
                'amr' => ['PROVIDER_KEY_SAML'],
                'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
            ],
        ];

        $token = new IntrospectToken(
            'token',
            'srn:cloud:idp:eu:0000000001:tenant',
            'https://apis.sugarcrm.com/auth/crm'
        );
        $token->setAttribute('platform', 'opi');
        $this->oAuthProvider->expects($this->once())
                            ->method('introspectToken')
                            ->with('token')
                            ->willReturn($introspectResult);
        $this->userProvider->expects($this->once())
                           ->method('loadUserBySrn')
                           ->with($introspectResult['sub'])
                           ->willReturn($this->user);
        $this->oAuthProvider->expects($this->once())
            ->method('getUserInfo')
            ->with('token')
            ->willReturn([
                'id' => 'seed_sally_id',
                'preferred_username' => 'test_name',
                'address' => [
                    'street_address' => 'test_street',
                ],
            ]);
        $this->userChecker->expects($this->once())->method('checkPostAuth')->with($this->user);
        $resultToken = $this->provider->authenticate($token);

        $this->assertInstanceOf(IntrospectToken::class, $resultToken);
        $this->assertEquals('opi', $resultToken->getAttribute('platform'));
        $this->assertEquals('token', $resultToken->getCredentials());
        $this->assertEquals('test_name', $resultToken->getUser()->getAttribute('oidc_data')['user_name']);
        $this->assertEquals('test_street', $resultToken->getUser()->getAttribute('oidc_data')['address_street']);
        $this->assertEquals('seed_sally_id', $resultToken->getUser()->getAttribute('oidc_identify')['value']);
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

    public function introspectTokenThrowsProvider()
    {
        $scopeExceptionMessage = 'Access token should contain https://apis.sugarcrm.com/auth/crm scope';
        $tidExceptionMessage = 'Access token does not belong to tenant srn:cloud:idp:eu:0000000001:tenant';
        $subExceptionMessage = 'Access token claims should belong to tenant srn:cloud:idp:eu:0000000001:tenant';
        $subSRNExceptionMessage = 'Invalid number of components in SRN';
        return [
            'noScope' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                ],
                'exceptionMessage' => $scopeExceptionMessage,
            ],
            'scopeIsEmpty' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => '',
                ],
                'exceptionMessage' => $scopeExceptionMessage,
            ],
            'scopeHasNoCrmScope' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => 'offline',
                ],
                'exceptionMessage' => $scopeExceptionMessage,
            ],
            'scopeHasNoCrmScopeButHasTwoOther' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => 'offline foo.bar',
                ],
                'exceptionMessage' => $scopeExceptionMessage,
            ],
            'scopesDelimitedIncorrectly' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    'scope' => 'offline,https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $scopeExceptionMessage,
            ],
            'tidIsEmpty' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'tid' => '',
                    'ext'=> [
                        'tid' => '',
                    ],
                    'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $tidExceptionMessage,
            ],
            'tidHasOtherTid' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cluster:iam:eu:0000000001:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000000:tenant',
                    ],
                    'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $tidExceptionMessage,
            ],
            'noSub' => [
                'response' => [
                    'active' => true,
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $subSRNExceptionMessage,
            ],
            'subIsEmpty' => [
                'response' => [
                    'active' => true,
                    'sub' => '',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $subSRNExceptionMessage,

            ],
            'subBelongsOtherTid' => [
                'response' => [
                    'active' => true,
                    'sub' => 'srn:cloud:idp:eu:0000000000:user:seed_max_id',
                    'ext'=> [
                        'tid' => 'srn:cloud:idp:eu:0000000001:tenant',
                    ],
                    'scope' => 'offline https://apis.sugarcrm.com/auth/crm',
                ],
                'exceptionMessage' => $subExceptionMessage,
            ],
        ];
    }

    /**
     * @covers ::authenticate
     * @dataProvider introspectTokenThrowsProvider
     */
    public function testIntrospectTokenThrows($response, $exceptionMessage)
    {
        $token = new IntrospectToken(
            'token',
            'srn:cloud:idp:eu:0000000001:tenant',
            'https://apis.sugarcrm.com/auth/crm'
        );

        $this->expectExceptionMessage($exceptionMessage);
        $this->oAuthProvider->expects($this->once())
            ->method('introspectToken')
            ->with('token')
            ->willReturn($response);

        $this->provider->authenticate($token);
    }
}

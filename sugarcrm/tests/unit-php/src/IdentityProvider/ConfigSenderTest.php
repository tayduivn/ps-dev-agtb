<?php
/**
 * Created by PhpStorm.
 * User: famchyk
 * Date: 9/6/17
 * Time: 6:23 PM
 */

namespace Sugarcrm\SugarcrmTestUnit\IdentityProvider;

use Sugarcrm\Sugarcrm\IdentityProvider\ConfigSender;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Message\Response as HttpResponse;
use Guzzle\Http\Message\Request as HttpRequest;

/**
 * Class ConfigSenderTest
 * @package Sugarcrm\SugarcrmTestUnit\IdentityProvider
 * @coversDefaultClass ConfigSender
 */
class ConfigSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $siteUrl = 'http://sugarcrm.host.local:8000';

    /**
     * @var string
     */
    protected $identityProviderUrl = 'http://idp.host.local:8000/';

    /**
     * @var ConfigSender
     */
    protected $configSender = null;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config = null;

    /**
     * @var HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient = null;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpResponse = null;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpRequest = null;

    /**
     * @var string
     */
    protected $clientSecrect = 'test:clientid:clientSecrect';

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSAMLConfig', 'getLdapConfig', 'get'])
            ->getMock();
        $this->config->method('get')->willReturnMap([
            ['oidc_oauth', null,
                ['idpUrl' => $this->identityProviderUrl,
                 'clientid:clientSecrect' => $this->clientSecrect,
                ],
            ],
            ['site_url', null, $this->siteUrl],
        ]);

        $this->httpClient = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['post'])
            ->getMock();

        $this->httpResponse = $this->getMockBuilder(HttpResponse::class)
            ->disableOriginalConstructor()
            ->setMethods(['isSuccessful'])
            ->getMock();
        $this->httpRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();
        $this->httpRequest->method('send')->willReturn($this->httpResponse);

        $this->configSender = new ConfigSender($this->config, $this->httpClient);
    }

    /**
     * Testing successful sending configuration.
     * @covers ::send
     */
    public function testSuccessfulSend()
    {
        $samlConfig = ['some', 'SAML', 'config'];
        $ldapConfig = ['some', 'LDAP', 'config'];
        $configEndpoint = $this->identityProviderUrl . 'config';
        $expectedMessage = ['data' =>
            ['instance' => $this->siteUrl,
                'config' => [
                    'enabledProviders' => ['local', 'saml', 'ldap'],
                    'local' => [],
                    'saml' => $samlConfig,
                    'ldap' => $ldapConfig,
                ],
            ],
            'signature' => '',
        ];

        $this->config
            ->expects($this->once())
            ->method('getSAMLConfig')
            ->willReturn($samlConfig);
        $this->config
            ->expects($this->once())
            ->method('getLdapConfig')
            ->willReturn($ldapConfig);
        $this->httpRequest->expects($this->once())
            ->method('send')
            ->willReturn($this->httpResponse);
        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo($configEndpoint),
                $this->equalTo(['Authorization' => 'Basic '.base64_encode($this->clientSecrect)]),
                $this->equalTo(json_encode($expectedMessage))
            )
            ->willReturn($this->httpRequest);
        $this->httpResponse->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(true);

        $this->configSender->send();
    }

    /**
     * Testing unsuccessful sending configuration.
     * @covers ::send
     * @expectedException \Exception
     * @expectedExceptionMessage Config was not sent to IdP
     */
    public function testUnSuccessfulSend()
    {
        $this->httpRequest->method('send')->willReturn($this->httpResponse);
        $this->httpClient->method('post')->willReturn($this->httpRequest);
        $this->httpResponse->expects($this->once())
            ->method('isSuccessful')
            ->willReturn(false);

        $this->configSender->send();
    }
}

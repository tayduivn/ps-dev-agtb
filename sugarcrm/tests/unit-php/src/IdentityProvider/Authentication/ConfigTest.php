<?php
namespace Sugarcrm\SugarcrmTestsUnit\IdentityProvider\Authentication;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

/**
 * @coversDefaultClass Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGet()
    {
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $sugarConfig->expects($this->any())
            ->method('get')
            ->willReturn('sugar_config_value');
        $config = new Config($sugarConfig);
        $this->assertEquals('sugar_config_value', $config->get('some_key'), 'Proxying to sugar config');

        $config->set('key', 'idm_value');
        $this->assertEquals('idm_value', $config->get('key'), 'Overridden value');
    }

    public function getSAMLConfigDataProvider()
    {
        return [
            'no override in config' => [
                [
                    'default' => 'config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' => 'config_site_url/index.php?module=Users&action=Authenticate&platform=base',
                        ],
                    ],
                ],
                ['default' => 'config'],
                [],
            ],
            'saml config provided' => [
                [
                    'default' => 'overridden config',
                    'sp' => [
                        'assertionConsumerService' => [
                            'url' => 'config_site_url/index.php?module=Users&action=Authenticate&platform=base',
                        ],
                    ],
                ],
                ['default' => 'config'],
                ['default' => 'overridden config'],
            ],
        ];
    }

    /**
     * @covers ::getSAMLConfig
     * @dataProvider getSAMLConfigDataProvider
     */
    public function testGetSAMLConfig($expectedConfig, $defaultConfig, $configValues)
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'getSAMLDefaultConfig'])
            ->getMock();
        $config->expects($this->any())
            ->method('get')
            ->withConsecutive(
                ['SAML_returnQueryVars'],
                ['SAML_SAME_WINDOW'],
                ['site_url'],
                ['SAML']
            )
            ->willReturnOnConsecutiveCalls(
                ['platform' => 'base'],
                'config_SAML_SAME_WINDOW',
                'config_site_url',
                $configValues
            );
        $config->expects($this->once())
            ->method('getSAMLDefaultConfig')
            ->willReturn($defaultConfig);
        $samlConfig = $config->getSAMLConfig();
        $this->assertEquals($expectedConfig, $samlConfig);
    }
}

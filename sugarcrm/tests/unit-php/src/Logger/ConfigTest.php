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

namespace Sugarcrm\Sugarcrm\Tests\Logger;

use Monolog\Logger;
use SugarConfig;
use Sugarcrm\Sugarcrm\Logger\Config;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Logger\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        SugarConfig::getInstance()->clearCache();
    }

    /**
     * @dataProvider getChannelConfigProvider
     * @covers ::getChannelConfig
     */
    public function testGetChannelConfig(array $config, $channel, $expected)
    {
        global $sugar_config;
        $sugar_config = $config;

        $sugarConfig = SugarConfig::getInstance();
        $sugarConfig->clearCache();

        $loggerConfig = new Config($sugarConfig);

        $params = $loggerConfig->getChannelConfig($channel);
        $this->assertEquals($expected, $params);
    }

    public static function getChannelConfigProvider()
    {
        return array(
            'default' => array(
                array(),
                'default',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'default-params' => array(
                array(
                    'logger' => array(
                        'handler' => 'gelf',
                        'level' => 'debug',
                    ),
                ),
                'default',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'gelf',
                            'level' => Logger::DEBUG,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handler-params' => array(
                array(
                    'logger' => array(
                        'handlers' => array(
                            'file' => array(
                                'dir' => '/var/log/sugar',
                            ),
                        ),
                    ),
                ),
                'default',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => array(
                                'dir' => '/var/log/sugar',
                            ),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'channel-params' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'elastica' => array(
                                'handlers' => array(
                                    'chrome' => array(
                                        'level' => 'debug',
                                    ),
                                    'file' => array(
                                        'level' => 'debug',
                                        'name' => 'elastica',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'elastica',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'chrome',
                            'level' => Logger::DEBUG,
                            'params' => array(),
                        ),
                        array(
                            'type' => 'file',
                            'level' => Logger::DEBUG,
                            'params' => array(
                                'name' => 'elastica',
                            ),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handlers-as-string' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-1' => array(
                                'handlers' => 'handler-1',
                            ),
                        ),
                    ),
                ),
                'channel-1',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handlers-as-array-of-types' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-1' => array(
                                'handlers' => array('handler-1', 'handler-2'),
                            ),
                        ),
                    ),
                ),
                'channel-1',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                        array(
                            'type' => 'handler-2',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handlers-as-numeric-array' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-1' => array(
                                'handlers' => array(
                                    array(
                                        'type' => 'handler-1',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'channel-1',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handlers-as-assoc-array' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-1' => array(
                                'handlers' => array(
                                    'handler-1' => array(),
                                ),
                            ),
                        ),
                    ),
                ),
                'channel-1',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-1',
                            'level' => Logger::ALERT,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'legacy-config' => array(
                array(
                    'log_dir' => '.',
                    'logger' => array(
                        'file' => array(
                            'ext' => '.log',
                            'name' => 'sugarcrm',
                            'dateFormat' => '%c',
                            'suffix' => '%Y_%m_%d',
                        ),
                    ),
                ),
                'default',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'file',
                            'level' => Logger::ALERT,
                            'params' => array(
                                'ext' => '.log',
                                'name' => 'sugarcrm',
                                'dateFormat' => '%c',
                                'suffix' => '%Y_%m_%d',
                            ),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handler-level-from-channel-handler' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-3' => array(
                                'handlers' => array(
                                    'handler-3' => array(
                                        'level' => 'info',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'channel-3',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-3',
                            'level' => Logger::INFO,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'handler-level-from-channel' => array(
                array(
                    'logger' => array(
                        'channels' => array(
                            'channel-4' => array(
                                'level' => 'warning',
                                'handlers' => 'handler-4',
                            ),
                        ),
                    ),
                ),
                'channel-4',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'handler-4',
                            'level' => Logger::WARNING,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
            'logger-off' => array(
                array(
                    'logger' => array(
                        'level' => 'off',
                    ),
                ),
                'default',
                array(
                    'handlers' => array(
                        array(
                            'type' => 'file',
                            'level' => 0,
                            'params' => array(),
                        ),
                    ),
                    'processors' => array(),
                ),
            ),
        );
    }
}

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

require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElasticIndexStrategyInterface.php';
require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElasticIndexStrategyBase.php';
require_once 'include/SugarSearchEngine/Elastic/SugarSearchEngineElasticIndexStrategySingle.php';

class SugarSearchEngineElasticIndexStrategyBaseTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Test if the settings set in the config are merged
     * with our default analyzers properly
     *
     * @dataProvider getIndexSettingsProvider
     * @param $params - ES Params
     */
    public function testGetIndexSettingsMerge($params, $addDefaults, $expected)
    {
        $search = new SugarSearchEngineElasticIndexStrategySingleTest();

        $settings = $search->getIndexSetting('test', $params, $addDefaults);

        $this->assertEquals($expected, $settings);
    }

    public static function getIndexSettingsProvider()
    {
        return array(
            0 => array(
                array(
                    'index_settings' => array(
                        'default' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'tokenizer' => 'whitespace',
                                            'filter' => array('lowercase')
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ),
                true,
                array(
                    'index' => array(
                        'analysis' => array(
                            'analyzer' => array(
                                'core_email_lowercase' => array(
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace',
                                    'filter' => array('lowercase')
                                ),
                            )
                        )
                    )
                )
            ),
            1 => array(
                array(
                    'index_settings' => array(
                        'default' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'tokenizer' => 'whitespace',
                                            'filter' => array('lowercase')
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ),
                false,
                array(
                    'index' => array(
                        'analysis' => array(
                            'analyzer' => array(
                                'core_email_lowercase' => array(
                                    'type' => 'custom',
                                    'tokenizer' => 'uax_url_email',
                                    'filter' => array(
                                        'lowercase',
                                    ),
                                ),
                            ),
                        ),
                    ),
                )
            ),
            2 => array(
                array(
                    'index_settings' => array(
                        'default' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'tokenizer' => 'whitespace',
                                            'filter' => array(0 => 'lowercase')
                                        ),
                                    )
                                )
                            )
                        ),
                        'test' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'filter' => array(0 => 'uppercase')
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ),
                true,
                array(
                    'index' => array(
                        'analysis' => array(
                            'analyzer' => array(
                                'core_email_lowercase' => array(
                                    'type' => 'custom',
                                    'tokenizer' => 'whitespace',
                                    'filter' => array(0 => 'uppercase')
                                ),
                            )
                        )
                    )
                )
            ),
            3 => array(
                array(
                    'index_settings' => array(
                        'default' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'tokenizer' => 'whitespace',
                                            'filter' => array(0 => 'lowercase')
                                        ),
                                    )
                                )
                            )
                        ),
                        'test' => array(
                            'index' => array(
                                'analysis' => array(
                                    'analyzer' => array(
                                        'core_email_lowercase' => array(
                                            'tokenizer' => 'keyword',
                                            'filter' => array(0 => 'uppercase')
                                        ),
                                    )
                                )
                            )
                        )
                    )
                ),
                true,
                array(
                    'index' => array(
                        'analysis' => array(
                            'analyzer' => array(
                                'core_email_lowercase' => array(
                                    'type' => 'custom',
                                    'tokenizer' => 'keyword',
                                    'filter' => array(0 => 'uppercase')
                                ),
                            )
                        )
                    )
                )
            ),
        );
    }
}

class SugarSearchEngineElasticIndexStrategySingleTest extends SugarSearchEngineElasticIndexStrategySingle
{
    public function getIndexSetting($indexName, $params = array(), $addDefaults = true)
    {
        return parent::getIndexSetting($indexName, $params, $addDefaults);
    }
}

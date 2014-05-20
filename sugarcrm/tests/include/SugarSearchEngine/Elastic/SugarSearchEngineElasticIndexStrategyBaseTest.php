<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

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

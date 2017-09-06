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

namespace Sugarcrm\SugarcrmTestsUnit\data\visibility;

use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \EmailsVisibility
 */
class EmailsVisibilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::elasticBuildMapping
     */
    public function testElasticBuildMapping()
    {
        $email = $this->createMock('\\Email');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();
        $mapping = new \Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping('Emails');

        $strategy = new \EmailsVisibility($email);
        $strategy->elasticBuildMapping($mapping, $provider);

        $properties = $mapping->compile();

        $expected = [
            'Emails__state' => [
                'type' => 'keyword',
                'index' => false,
                'fields' => [
                    'emails_state' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'state' => [
                'type' => 'keyword',
                'index' => false,
                'copy_to' => [
                    'Emails__state',
                ],
            ],
        ];
        $this->assertEquals($expected, $properties);
    }

    /**
     * @covers ::elasticGetBeanIndexFields
     */
    public function testElasticGetBeanIndexFields()
    {
        $email = $this->createMock('\\Email');
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();

        $strategy = new \EmailsVisibility($email);
        $fields = $strategy->elasticGetBeanIndexFields('Emails', $provider);

        $this->assertEquals(['state' => 'enum'], $fields);
    }

    /**
     * @covers ::elasticAddFilters
     */
    public function testElasticAddFilters()
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();

        $email = $this->createMock('\\Email');
        $filter = new \Elastica\Query\BoolQuery();
        $provider = new \Sugarcrm\Sugarcrm\Elasticsearch\Provider\Visibility\Visibility();

        $strategy = new \EmailsVisibility($email);
        $strategy->elasticAddFilters($user, $filter, $provider);

        $query = $filter->toArray();

        $expected = [
            'bool' => [
                'must' => [
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'Emails__state.emails_state' => [
                                                        'value' => 'Draft',
                                                        'boost' => 1,
                                                    ],
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'Common__owner_id.owner' => [
                                                        'value' => $user->id,
                                                        'boost' => 1.0,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            [
                                                'term' => [
                                                    'Emails__state.emails_state' => [
                                                        'value' => 'Draft',
                                                        'boost' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertEquals($expected, $query);
    }
}

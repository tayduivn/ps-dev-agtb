<?php
//FILE SUGARCRM flav=ent ONLY
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

// namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine\PMSEHandlers;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEBeanHandler
 */
class PMSEBeanHandlerTest extends TestCase
{
    /**
     * The PMSEBeanHandler object
     * @var PMSEBeanHandler
     */
    protected static $bh;

    /**
     * @inheritDoc
     */
    public static function setupBeforeClass()
    {
        static::$bh = new PMSEBeanHandler();
    }

    /**
     * Data Provider for the parseString test method
     * @return array
     */
    public function parseStringDataProvider()
    {
        return [
            // No content to parse
            [
                'template' => 'Hello world',
                'base_module' => 'Foo',
                'expect' => [
                    'Foo' => [],
                ],
            ],
            // Module field
            [
                'template' => 'Foo {::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_future' => [
                            'value_type' => 'future',
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'original' => '{::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Module field old value
            [
                'template' => 'Foo {::Accounts::name::old::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_old' => [
                            'value_type' => 'old',
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'original' => '{::Accounts::name::old::}',
                        ],
                    ],
                ],
            ],
            // Target record link
            [
                'template' => 'Foo {::href_link::Accounts::name::} bar',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'name_href_link' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::name::}',
                        ],
                    ],
                ],
            ],
            // Related record link
            [
                'template' => '{::href_link::Accounts::campaign_accounts::name::}',
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [],
                    'campaign_accounts' => [
                        'name' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::campaign_accounts::name::}',
                            'type' => 'relate',
                            'rel_module' => 'campaign_accounts',
                        ],
                    ],
                ],
            ],
            // All parsed parts in a template
            [
                'template' => $this->getFullTemplate(),
                'base_module' => 'Accounts',
                'expect' => [
                    'Accounts' => [
                        'Accounts_name_future' => [
                            'value_type' => 'future',
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'original' => '{::Accounts::name::}',
                        ],
                        'Accounts_name_old' => [
                            'value_type' => 'old',
                            'filter' => 'Accounts',
                            'name' => 'name',
                            'original' => '{::Accounts::name::old::}',
                        ],
                        'campaign_accounts_name_future' => [
                            'value_type' => 'future',
                            'filter' => 'campaign_accounts',
                            'name' => 'name',
                            'original' => '{::campaign_accounts::name::}',
                        ],
                        'name_href_link' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::name::}',
                        ],
                    ],
                    'campaign_accounts' => [
                        'name' => [
                            'name' => 'name',
                            'value_type' => 'href_link',
                            'original' => '{::href_link::Accounts::campaign_accounts::name::}',
                            'type' => 'relate',
                            'rel_module' => 'campaign_accounts',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getFullTemplate()
    {
        return <<<TMP
Hello {::Accounts::name::},

You recently updated your name from {::Accounts::name::old::}. Your {::campaign_accounts::name::} has also been updated.

Your record: {::href_link::Accounts::name::}
Your campaign: {::href_link::Accounts::campaign_accounts::name::}
TMP;
    }

    /**
     * Tests parseString
     * @param string $template String to parse.
     * @param string $base_module name of the module.
     * @param array $expect Expectations
     * @dataProvider parseStringDataProvider
     * @covers ::parseString
     */
    public function testParseString($template, $base_module, $expect)
    {
        $result = static::$bh->parseString($template, $base_module);
        $this->assertEquals($expect, $result);
    }
}

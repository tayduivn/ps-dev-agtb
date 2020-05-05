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

use PHPUnit\Framework\TestCase;

class Bug43452Test extends TestCase
{
    protected function setUp() : void
    {
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }
    
    protected function tearDown() : void
    {
        unset($GLOBALS['app_strings']);
    }
    
    /**
     * @ticket 43452
     */
    public function testGenerateSearchWhereWithUnsetBool()
    {
        global $db;
        // Looking for a NON Converted Lead named "Fabio".
        // Without changes, PopupSmarty return a bad query, with AND and OR at the same level.
        // With this fix we get parenthesis:
        //     1) From SearchForm2->generateSearchWhere, in case of 'bool' (they surround "converted = '0' or converted IS NULL")
        //     2) From PopupSmarty->_get_where_clause, when items of where's array are imploded.

        $_searchFields['Leads'] =  ['first_name'=> ['value' => 'Fabio', 'query_type'=>'default'],
                                         'converted'=> ['type'=> 'bool', 'value' => '0', 'query_type'=>'default'],
                                        ];
        // provides $searchdefs['Leads']
        $searchdefs = [];
        require "modules/Leads/metadata/searchdefs.php";
        
        $bean = BeanFactory::newBean('Leads');
        $popup = new PopupSmarty($bean, "Leads");
        $popup->searchForm->searchdefs =  $searchdefs['Leads'];
        $popup->searchForm->searchFields = $_searchFields['Leads'];
        $tWhere = $popup->_get_where_clause();

        $this->assertStringContainsString('(leads.converted = 0 OR leads.converted IS NULL)', $tWhere);
        $this->assertStringContainsString($db->getLikeSQL('leads.first_name', 'Fabio%'), $tWhere);
    }
}

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

require_once "modules/ModuleBuilder/parsers/views/AbstractMetaDataParser.php";

/**
 * Bug #39729
 * "Email Address field is not avialable in the ToolBox if removed from Leeds > Convert Leeds > Contacts Layout"
 *
 * @author Mikhail Yarotsky
 * @ticket 39729
 */
class Bug39729Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var $_view;
     */
    private $_view;

    /**
     * @var $def;
     */
    private $def;

    public function setUp()
    {
        global $dictionary;
        $this->_view = 'editview';
        VardefManager::loadVardef('Contacts', 'Contact');
        $this->def = $dictionary['Contact']['fields']['email1'];

    }

    public function tearDown()
    {
        unset($this->_view);
        unset($this->def);
    }

    /**
     * Relate to email1 should be true
     * @group 39729
     */
    public function testEmail1FieldOnTrue()
    {
        $this->assertTrue(AbstractMetaDataParser::validField ( $this->def,  $this->_view ));
    }
}

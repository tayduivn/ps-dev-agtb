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

class RenameModulesTest extends TestCase
{
    private $language = 'en_us';
    private $language_contents;
    private $global_language_contents;

    protected function setUp() : void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $mods = array('Accounts', 'Contacts', 'Campaigns');
        foreach($mods as $mod)
        {
            if(file_exists("custom/modules/{$mod}/language/en_us.lang.php"))
            {
                $this->language_contents[$mod] = file_get_contents("custom/modules/{$mod}/language/en_us.lang.php");
                unlink("custom/modules/{$mod}/language/en_us.lang.php");
            }
        }

        // check the global lang file
        if (file_exists("custom/include/language/" . $this->language . ".lang.php")) {
            $this->global_language_contents = file_get_contents("custom/include/language/" . $this->language . ".lang.php");
        }
    }

    protected function tearDown() : void
    {
        $this->removeCustomAppStrings();
        $this->removeModuleStrings(array('Accounts'));
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarCache::$isCacheReset = false;

        if(!empty($this->language_contents))
        {
            foreach($this->language_contents as $key=>$contents)
            {
                file_put_contents("custom/modules/{$key}/language/en_us.lang.php", $contents);
            }
        }

        if(!empty($this->global_language_contents)) {
            file_put_contents(
                "custom/include/language/" . $this->language . ".lang.php",
                $this->global_language_contents
            );
        }
        SugarTestHelper::tearDown();
    }

    public function testGetRenamedModules()
    {
        $rm = new RenameModules();
        $this->assertEquals(0, count($rm->getRenamedModules()) );
    }

    private function removeCustomAppStrings()
    {
        $fileName = 'custom/include/language/' . $this->language . '.lang.php';
        if( file_exists($fileName) )
        {
            @unlink($fileName);
        }
    }

    private function removeModuleStrings($modules)
    {
        foreach($modules as $module => $v)
        {
            $fileName = 'custom/modules/' . $module . '/language/' . $this->language . '.lang.php';
            if( file_exists($fileName) )
            {
                @unlink($fileName);
            }
        }
    }

    /**
     * Provide test data for renaming module-related strings.
     *
     * @return array
     */
    public function fieldNameProvider()
    {
        return array(
            // Test empty label.
            array('', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), '', ''),
            // Test whole words.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client', 'Client'),
            array('Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Clients', 'Clients'),
            // Test empty field values.
            array('Contacts', array('prev_singular' => '', 'prev_plural' => '', 'singular' => '', 'plural' => ''), 'Contacts', 'Contacts'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => '', 'singular' => 'Client', 'plural' => 'Clients'), 'Contact', 'Contact'),
            array('Contacts', array('prev_singular' => '', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => 'Clients'), 'Contacts', 'Contacts'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'), 'Contact', 'Contact'),
            array('Contacts', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''), 'Contacts', 'Contacts'),
            array('Contacts', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => '', 'plural' => 'Clients'), 'Contacts', 'Clients'),
            array('Contact', array('prev_singular' => 'Contact', 'prev_plural' => 'Contacts', 'singular' => 'Client', 'plural' => ''), 'Client', 'Contact'),
            // Test multiple words in labels.
            array('My Account:', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'My Client:', 'My Client:'),
            array('View Accounts Module', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'View Clients Module', 'View Clients Module'),
            // Test labels without previous values.
            array('View Module', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'View Module', 'View Module'),
            array('Settings', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Settings', 'Settings'),
            // Test multiple replacements.
            array('Account Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Clients', 'Client Clients'),
            array('Account Accounts Account', array('prev_singular' => 'Account', 'prev_plural' => 'Accounts', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Clients Client', 'Client Clients Client'),
            // Test labels with same previous values.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'), 'Client', 'Clients'),
            array('Account Accounts', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => 'Client', 'plural' => 'Clients'), 'Client Accounts', 'Clients Accounts'),
            // Test fields with special characters.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '<script>alert("hello");</script>', 'plural' => ''), 'alert(&quot;hello&quot;);', 'Account'),
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => '', 'plural' => '<script>alert("hello");</script>'), 'Account', 'alert(&quot;hello&quot;);'),
            // Test fields with only spaces.
            array('Account', array('prev_singular' => 'Account', 'prev_plural' => 'Account', 'singular' => ' ', 'plural' => ' '), 'Account', 'Account')
        );
    }

    /**
     * Test renaming module-related string functionality.
     *
     * @dataProvider fieldNameProvider
     */
    public function testModuleRelatedStringRenaming($label, $renameFields, $newLabel, $newLabelPluralFirst)
    {
        $rm = new RenameModules();

        // Perform the same sanitization checks done during the actual request.
        $renameFields['singular'] = SugarCleaner::stripTags($renameFields['singular']);
        $renameFields['plural'] = SugarCleaner::stripTags($renameFields['plural']);
        $renameFields['singular'] = trim($renameFields['singular']);
        $renameFields['plural'] = trim($renameFields['plural']);

        $renamedLabelSingularFirst = $rm->renameModuleRelatedStrings($label, $renameFields, false);
        $renamedLabelDefault = $rm->renameModuleRelatedStrings($label, $renameFields);

        $this->assertEquals($newLabel, $renamedLabelSingularFirst);
        $this->assertEquals($newLabelPluralFirst, $renamedLabelDefault);
    }
}

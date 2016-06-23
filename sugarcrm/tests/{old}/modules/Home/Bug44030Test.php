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

class Bug44030Test extends TestCase
{
	var $unified_search_modules_file;

    public function setUp()
    {
	    global $beanList, $beanFiles, $dictionary;

	    //Add entries to simulate custom module
	    $beanList['Bug44030_TestPerson'] = 'Bug44030_TestPerson';
	    $beanFiles['Bug44030_TestPerson'] = 'modules/Bug44030_TestPerson/Bug44030_TestPerson.php';

	    VardefManager::loadVardef('Contacts', 'Contact');
	    $dictionary['Bug44030_TestPerson'] = $dictionary['Contact'];

	    //Copy over custom SearchFields.php file
        if(!file_exists('custom/modules/Bug44030_TestPerson/metadata')) {
       		mkdir_recursive('custom/modules/Bug44030_TestPerson/metadata');
    	}

    if( $fh = @fopen('custom/modules/Bug44030_TestPerson/metadata/SearchFields.php', 'w+') )
    {
$string = <<<EOQ
<?php
\$searchFields['Bug44030_TestPerson']['email'] = array(
'query_type' => 'default',
'operator' => 'subquery',
'subquery' => 'SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE',
'db_field' => array('id',),
'vname' =>'LBL_ANY_EMAIL',
);
?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }

    }

    public function tearDown()
    {
	    global $beanList, $beanFiles, $dictionary;

		if(file_exists($this->unified_search_modules_file . '.bak'))
		{
			copy($this->unified_search_modules_file . '.bak', $this->unified_search_modules_file);
			unlink($this->unified_search_modules_file . '.bak');
		}

		if(file_exists('custom/modules/Bug44030_TestPerson/metadata/SearchFields.php'))
		{
			unlink('custom/modules/Bug44030_TestPerson/metadata/SearchFields.php');
			rmdir_recursive('custom/modules/Bug44030_TestPerson');
		}
		unset($beanFiles['Bug44030_TestPerson']);
		unset($beanList['Bug44030_TestPerson']);
		unset($dictionary['Bug44030_TestPerson']);
    }

	public function testUnifiedSearchAdvancedBuildCache()
	{
		$usa = new UnifiedSearchAdvanced();
        $unified_search_modules= $usa->buildCache();

        $this->assertArrayHasKey(
            'Bug44030_TestPerson',
            $unified_search_modules,
            "Assert that we have the custom module set in unified_search_modules cache."
        );
        $this->assertArrayHasKey(
            'email',
            $unified_search_modules['Bug44030_TestPerson']['fields'],
            "Assert that the email field was set for the custom module"
        );
    }

}

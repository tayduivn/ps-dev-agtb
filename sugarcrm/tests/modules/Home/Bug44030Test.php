<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
class Bug44030Test extends Sugar_PHPUnit_Framework_TestCase
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
	    
	    
	    //Remove the cached unified_search_modules.php file
	    $this->unified_search_modules_file = $GLOBALS['sugar_config']['cache_dir'] . 'modules/unified_search_modules.php';
    	if(file_exists($this->unified_search_modules_file))
		{
			copy($this->unified_search_modules_file, $this->unified_search_modules_file.'.bak');
			unlink($this->unified_search_modules_file);
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
		require_once('modules/Home/UnifiedSearchAdvanced.php');
		$usa = new UnifiedSearchAdvanced();
		$usa->buildCache();
		
		//Assert we could build the file without problems
		$this->assertTrue(file_exists($this->unified_search_modules_file), "Assert {$this->unified_search_modules_file} file was created");
	
	    include($this->unified_search_modules_file);
	    $this->assertTrue(isset($unified_search_modules['Bug44030_TestPerson']), "Assert that we have the custom module set in unified_search_modules.php file");
	    $this->assertTrue(isset($unified_search_modules['Bug44030_TestPerson']['fields']['email']), "Assert that the email field was set for the custom module");
	}

}

?>
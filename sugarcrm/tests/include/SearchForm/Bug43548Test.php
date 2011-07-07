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
 
require_once 'include/SearchForm/SugarSpot.php';

class Bug43548Test extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    	if(file_exists('custom/modules/Accounts/metadata/SearchFields.php'))
    	{
    	   copy('custom/modules/Accounts/metadata/SearchFields.php', 'custom/modules/Accounts/metadata/SearchFields.php.bak');
    	} else {
    	   if(!file_exists('custom/modules/Accounts/metadata'))
    	   {
    	      mkdir_recursive('custom/modules/Accounts/metadata');
    	   }
    	}    	

    }
    
    public function tearDown()
    {
        if(file_exists('custom/modules/Accounts/metadata/SearchFields.php'))
    	{
    	   unlink('custom/modules/Accounts/metadata/SearchFields.php');
    	} 

    	if(file_exists('custom/modules/Accounts/metadata/SearchFields.php.bak'))
    	{
    	   copy('custom/modules/Accounts/metadata/SearchFields.php.bak', 'custom/modules/Accounts/metadata/SearchFields.php');
    	   unlink('custom/modules/Accounts/metadata/SearchFields.php.bak');
    	}
    }

    
    public function testSugarSpotSearchGetSearchFieldsWithInline()
    {
    	//Load custom file with inline style of custom overrides
    if( $fh = @fopen('custom/modules/Accounts/metadata/SearchFields.php', 'w+') )
	{
$string = <<<EOQ
<?php
\$searchFields['Accounts']['account_type'] = array('query_type'=>'default', 'options' => 'account_type_dom', 'template_var' => 'ACCOUNT_TYPE_OPTIONS');
?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }        	
    	$spot = new SugarSpotMock();
    	$searchFields = $spot->getTestSearchFields('Accounts');
    	$this->assertTrue(isset($searchFields['Accounts']['name']), 'Assert that name field is still set');
    	$this->assertTrue(isset($searchFields['Accounts']['account_type']), 'Assert that account_type field is still set');
    }
    
    public function testSugarSpotGetSearchFieldsWithCustomOverride()
    {
    	//Load custom file with override style of custom overrides
    if( $fh = @fopen('custom/modules/Accounts/metadata/SearchFields.php', 'w+') )
	{
$string = <<<EOQ
<?php

\$searchFields['Accounts'] = 
	array (
		'name' => array( 'query_type'=>'default'),
		'account_type'=> array('query_type'=>'default', 'options' => 'account_type_dom', 'template_var' => 'ACCOUNT_TYPE_OPTIONS'),
    );

?>
EOQ;
       fputs( $fh, $string);
       fclose( $fh );
    }    
    
    	$spot = new SugarSpotMock();
    	$searchFields = $spot->getTestSearchFields('Accounts');
    	$this->assertTrue(isset($searchFields['Accounts']['name']), 'Assert that name field is still set');
    	$this->assertTrue(isset($searchFields['Accounts']['account_type']), 'Assert that account_type field is still set');    	
    }
    
    
}

//Create SugarSpotMock since getSearchFields is protected
class SugarSpotMock extends SugarSpot {
	function getTestSearchFields($moduleName)
	{
		return parent::getSearchFields($moduleName);
	}
}

?>
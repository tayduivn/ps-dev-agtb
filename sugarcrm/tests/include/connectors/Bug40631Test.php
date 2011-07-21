<?php
//FILE SUGARCRM flav=pro ONLY

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
require_once('include/connectors/ConnectorFactory.php');
require_once('include/connectors/sources/SourceFactory.php');
require_once('include/connectors/formatters/FormatterFactory.php');
require_once('tests/include/connectors/HooversHelper.php');

/**
 * @outputBuffering enabled
 */
class Bug40631Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $has_original_metadata_custom_directory;
    private $has_original_hoovers_custom_directory;

    public function setUp()
    {
        if(is_dir('custom/modules/Connectors/connectors/sources/ext/soap/hoovers'))
        {
           $this->has_original_hoovers_custom_directory = true;
           //backup directory
           mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
           copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
        } else {
           mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
        }

        //Now create the test files with the pre 3.3 version
        //1) Create hoovers_custom_functions.php
        $the_string = <<<EOQ
<?php

/**
 * get_country_value
 *
 */
function get_country_value(\$bean, \$out_field, \$value) {
	if(file_exists('include/language/en_us.lang.php')) {
	   require('include/language/en_us.lang.php');
	   if(isset(\$app_list_strings['countries_dom'])) {
	   	  \$country = trim(strtoupper(\$value));
	   	  if(isset(\$app_list_strings['countries_dom'][\$country])) {
	   	  	 return \$app_list_strings['countries_dom'][\$country];
	   	  }
	   }
	}

    switch(\$country) {
     	case (preg_match('/U[\.]?S[\.]?A[\.]?/', \$country) || \$country == 'UNITED STATES' || \$country == 'AMERICA' || \$country == 'NORTH AMERICA') :
     	    return "USA";
     	case (\$country == "ENGLAND" || \$country == "UK" || \$country == "GREAT BRITAIN" || \$country == "BRITAIN") :
     		return "UNITED KINGDOM";
     	default :
     		return \$value;
    }
}


/**
 * get_hoovers_finsales
 *
 * @param \$value decimal number denoting annual sales in millions of dollars
 */
function get_hoovers_finsales(\$bean, \$out_field, \$value) {

	\$value = trim(\$value);
	if(empty(\$value) || !is_numeric(\$value) || \$value == '0'){
			return 'Unknown';
	}

	\$value = \$value * 1000000;	//Multiply by 1 million
	\$value = intval(floor(\$value));

	switch(\$value) {
		case (\$value < 10000000):
			return 'under 10M';
		case (\$value < 25000000):
			return '10 - 25M';
		case (\$value < 100000000):
			return '25 - 99M';
		case (\$value < 250000000):
			return '100M - 249M';
		case (\$value < 500000000):
			return '250M - 499M';
		case (\$value < 1000000000):
			return '500M - 1B';
		case (\$value >= 1000000000):
			return 'more than 1B';
		default:
			return 'Unknown';
	}
}

function get_hoovers_employees(\$bean, \$out_field, \$value) {

	\$value = trim(\$value);
	if(empty(\$value) || !is_numeric(\$value)) {
	   return '';
	}

	switch(\$value) {
		case (\$value < 100):
			return 'under 100 employees';
		case (\$value < 400):
			return '100 - 399 employees';
		case (\$value < 1000):
			return '400 - 999 employees';
		default:
			return 'more than 1000 employees';
	}
}

?>
EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //2) Create listviewdefs.php
        $the_string = <<<EOQ
<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

\$listViewDefs['ext_soap_hoovers'] = array(
	'recname' => array(
		'width' => '25',
		),
	'locationtype' => array(
		'width' => '15',
		),
	'addrcity' => array(
		'width' => '15',
		),
	'addrstateprov' => array(
		'width' => '15',
		),
	'country' => array(
		'width' => '10',
		),
	'hqphone' => array(
		'width' => '10',
		),
	'finsales' => array(
        'width' => '10',
		),

);
?>
EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/listviewdefs.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //3) Create mapping.php
        $the_string = <<<EOQ
<?php
// created: 2010-11-10 10:12:43
\$mapping = array (
  'beans' =>
  array (
      'Accounts' => array (
            'id' => 'id',
		  	'recname' => 'name',
            'addrstreet1' => 'billing_address_street',
            'addrstreet2' => 'billing_address_street_2',
		    'addrcity' => 'billing_address_city',
		    'addrstateprov' => 'billing_address_state',
		    'addrcountry' => 'billing_address_country',
		    'addrcity' => 'billing_address_city',
		    'addrzip' => 'billing_address_postalcode',
            'finsales' => 'annual_revenue',
            'employees' => 'employees',
            'hqphone' => 'phone_office',
      		'description' => 'description',
      ),
      'Contacts' => array(
            'id' => 'id',
            'recname' => 'company_name',
            'addrstreet1' => 'primary_address_street',
            'addrstreet2' => 'primary_address_street_2',
		    'addrcity' => 'primary_address_city',
		    'addrstateprov' => 'primary_address_state',
		    'addrcountry' => 'primary_address_country',
		    'addrcity' => 'primary_address_city',
		    'addrzip' => 'primary_address_postalcode',
            'hqphone' => 'phone_work',
      ),
  ),
);
?>

EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/mapping.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //4) Create vardefs.php
        $the_string = <<<EOQ
<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

\$dictionary['ext_soap_hoovers'] = array(

  'comment' => 'vardefs for hoovers connector',
  'fields' => array (
    'id' =>
	  array (
	    'name' => 'id',
	    'input' => 'uniqueId',
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'hidden' => true,
	    'comment' => 'Unique identifier for records'
	),
    'recname'=> array(
	    'name' => 'recname',
		'input' => 'bal.specialtyCriteria.companyKeyword',
		'output' => 'recname',
	    'vname' => 'LBL_NAME',
	    'type' => 'varchar',
	    'search' => true,
	    'comment' => 'The name of the company',
    ),
   'duns' => array (
	    'name' => 'duns',
    	'input' => 'bal.specialtyCriteria.duns',
		'output' => 'duns',
	    'vname' => 'LBL_DUNS',
	    'type' => 'varchar',
    	'hidden' => true,
	    'search' => true,
	    'comment' => 'The duns id used by Hoovers',
    ),
   'parent_duns' => array (
	    'name' => 'parent_duns',
		'output' => 'parent_duns',
	    'vname' => 'LBL_PARENT_DUNS',
	    'type' => 'varchar',
	    'comment' => 'The parent duns id used by Hoovers',
	    'search' => true,
    	'required' => true,
    	'hidden' => true,
    ),
   'addrcity' => array (
	    'name' => 'addrcity',
   		'input' => 'bal.location.city',
   		'output' => 'addrcity',
	    'vname' => 'LBL_CITY',
	    'type' => 'varchar',
	    'search' => true,
	    'comment' => 'The city address for the company',
   ),
   'addrstreet1' => array(
        'name' => 'addrstreet1',
        'search' => false,
        'vname' => 'LBL_ADDRESS_STREET1',
        'type' => 'varchar',
        'comment' => 'street address',
   ),
   'addrstreet2' => array(
        'name' => 'addrstreet2',
        'search' => false,
        'vname' => 'LBL_ADDRESS_STREET2',
        'type' => 'varchar',
        'comment' => 'street address (continued)',
   ),
   'addrstateprov' => array(
        'name' => 'addrstateprov',
   		'input' => 'bal.location.state', //\$args['bal']['location']['state'] = 'California'
   		'output' => 'addrstateprov',
        'vname' => 'LBL_STATE',
        'type' => 'varchar',
        'search' => true,
        'options' => 'addrstateprov_dom',
        'comment' => 'The state address for the company',
   ),
   'addrcountry' => array(
        'name' => 'addrcountry',
        'input' => 'bal.location.country',
        'vname' => 'LBL_COUNTRY',
        'type' => 'varchar',
        'search' => true,
        'comment' => 'The country address for the company',
        'function' => array('name'=>'get_country_value', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),
   ),
   'addrzip' => array(
        'name' => 'addrzip',
   		'input' => 'bal.location.zip',
   		'output' => 'addrzip',
        'vname' => 'LBL_ZIP',
        'type' => 'varchar',
        'search' => true,
        'comment' => 'The postal code address for the company',
   ),
   'finsales' => array(
        'name' => 'finsales',
        'vname' => 'LBL_FINSALES',
        'type' => 'enum',
        'comment' => 'Annual sales (in millions)',
        'function' => array('name'=>'get_hoovers_finsales', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),
   ),
   /*
   'locationtype' => array(
        'name' => 'locationtype',
        'vname' => 'LBL_LOCATION_TYPE',
        'type' => 'varchar',
        'comment' => 'Location type such as headquarters or branch',
   ),
   'companytype' => array(
        'name' => 'companytype',
        'vname' => 'LBL_COMPANY_TYPE',
        'type' => 'varchar',
        'comment' => 'Company type (public, private, etc.)',
   ),
   */
   'hqphone' => array(
        'name' => 'hqphone',
        'vname' => 'LBL_HQPHONE',
        'type' => 'varchar',
        'comment' => 'Headquarters phone number',
   ),
   'employees' => array(
        'name' => 'employees',
        'vname' => 'LBL_TOTAL_EMPLOYEES',
        'type' => 'decimal',
        'comment' => 'Total number of employees',
        'function' => array('name'=>'get_hoovers_employees', 'include'=>'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'),
   ),
   )
);
?>
EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/vardefs.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //Create config.php
        //5) Create config.php
        $the_string = <<<EOQ
<?php
\$config['name'] = 'Hoovers&#169;';
\$config['order'] = 2;
\$config['properties']['hoovers_endpoint'] = 'http://hapi.hoovers.com/axis2/services/AccessHoovers';
\$config['properties']['hoovers_wsdl'] = 'http://hapi.hoovers.com/axis2/Hapi.wsdl';
\$config['properties']['hoovers_api_key'] = '';
?>
EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );


        if(is_dir('custom/modules/Connectors/metadata'))
        {
           $this->has_original_metadata_custom_directory = true;
           //backup directory
           mkdir_recursive('custom/modules/Connectors/metadata_bak');
           copy_recursive('custom/modules/Connectors/metadata', 'custom/modules/Connectors/metadata_bak');
        } else {
           mkdir_recursive('custom/modules/Connectors/metadata');
        }


        //1) Create mergeviewdefs.php
        $the_string = <<<EOQ
<?php
\$viewdefs = array(
  'Connector'=> array('MergeView'=>
     array('Touchpoints'=>
        array(
              'company_name',
              'primary_address_street',
              'primary_address_city',
              'primary_address_state',
              'primary_address_country',
              'primary_address_postalcode',
              'annual_revenue',
              'employees',
              'description',
        ),
     ),
  ),
);

EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/metadata/mergeviewdefs.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //2) Create searchdefs.php
        $the_string = <<<EOQ
<?php
\$searchdefs = array (
  /*
  'ext_rest_zoominfocompany' =>
  array (
    'Touchpoints' =>
    array (
      0 => 'companyname',
      1 => 'countrycode',
      2 => 'zip',
    ),
  ),
  'ext_soap_jigsaw' =>
  array (
    'Touchpoints' =>
    array (
      0 => 'name',
    ),
  ),
  */
  'ext_soap_hoovers' =>
  array (
    'Touchpoints' =>
    array (
      0 => 'recname',
      1 => 'addrstateprov',
      2 => 'addrcountry',
    ),
    'Accounts' =>
    array (
      0 => 'recname',
    ),
    'Opportunities' =>
    array (
      0 => 'recname',
    ),
    'Contacts' =>
    array (
      0 => 'recname',
    ),
  ),
  'ext_rest_linkedin' =>
  array (
    'Accounts' =>
    array (
    ),
    'Opportunities' =>
    array (
    ),
  ),
);
?>

EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/metadata/searchdefs.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //3) Create connectors.php
        $the_string = <<<EOQ

<?php
// created: 2010-10-01 11:49:49
\$connectors = array (
  'ext_rest_linkedin' =>
  array (
    'id' => 'ext_rest_linkedin',
    'name' => 'LinkedIn&#169;',
    'enabled' => true,
    'directory' => 'modules/Connectors/connectors/sources/ext/rest/linkedin',
    'modules' =>
    array (
      0 => 'Accounts',
      1 => 'Opportunities',
    ),
  ),
  'ext_soap_hoovers' =>
  array (
    'id' => 'ext_soap_hoovers',
    'name' => 'Hoovers&#169;',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers',
    'modules' =>
    array (
      0 => 'Touchpoints',
      1 => 'Accounts',
      2 => 'Opportunities',
      3 => 'Contacts',
    ),
  ),
  /*
  'ext_rest_zoominfocompany' =>
  array (
    'id' => 'ext_rest_zoominfocompany',
    'name' => 'Zoominfo&#169; - Company',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/rest/zoominfocompany',
    'modules' =>
    array (
      0 => 'Touchpoints',
    ),
  ),
  'ext_rest_zoominfoperson' =>
  array (
    'id' => 'ext_rest_zoominfoperson',
    'name' => 'Zoominfo&#169; - Person',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/rest/zoominfoperson',
    'modules' =>
    array (
    ),
  ),
  'ext_rest_crunchbase' =>
  array (
    'id' => 'ext_rest_crunchbase',
    'name' => 'Crunchbase&#169;',
    'enabled' => true,
    'directory' => 'modules/Connectors/connectors/sources/ext/rest/crunchbase',
    'modules' =>
    array (
    ),
  ),
  'ext_soap_jigsaw' =>
  array (
    'id' => 'ext_soap_jigsaw',
    'name' => 'Jigsaw&#169;',
    'enabled' => true,
    'directory' => 'custom/modules/Connectors/connectors/sources/ext/soap/jigsaw',
    'modules' =>
    array (
      0 => 'Touchpoints',
    ),
  ),
  */
);
?>

EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/metadata/connectors.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        //4) display_config.php
        $the_string = <<<EOQ
<?php
// created: 2010-07-19 12:56:38
\$modules_sources = array (
  'Touchpoints' =>
  array (
    //'ext_rest_zoominfocompany' => 'ext_rest_zoominfocompany',
    //'ext_soap_jigsaw' => 'ext_soap_jigsaw',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Accounts' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Opportunities' =>
  array (
    'ext_rest_linkedin' => 'ext_rest_linkedin',
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
  'Contacts' =>
  array (
    'ext_soap_hoovers' => 'ext_soap_hoovers',
  ),
);
?>

EOQ;

        $fp = sugar_fopen('custom/modules/Connectors/metadata/display_config.php', "w" );
        fwrite( $fp, $the_string );
        fclose( $fp );

        mkdir_recursive(dirname('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/lookup_mapping.php'));
	    copy(dirname(__FILE__)."/lookup_mapping_stub", 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers/lookup_mapping.php');
    }

    public function tearDown()
    {
        if($this->has_original_hoovers_custom_directory)
        {
           //Remove custom directory
           rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
           //Re-create custom directory
           mkdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
           //Copy original contents back in
           copy_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak', 'custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
           //Remove the backup directory
           rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers_bak');
        } else {
           //Remove the custom directory
           rmdir_recursive('custom/modules/Connectors/connectors/sources/ext/soap/hoovers');
        }

        if($this->has_original_metadata_custom_directory)
        {
           //Remove custom directory
           rmdir_recursive('custom/modules/Connectors/metadata');
           //Re-create custom directory
           mkdir_recursive('custom/modules/Connectors/metadata');
           //Copy original contents back in
           copy_recursive('custom/modules/Connectors/metadata_bak', 'custom/modules/Connectors/metadata');
           //Remove the backup directory
           rmdir_recursive('custom/modules/Connectors/metadata_bak');
        } else {
           //Remove the custom directory
           rmdir_recursive('custom/modules/Connectors/metadata');
        }
    }


    private function getResultData($filename)
    {
    	$result = '';
    	require(dirname(__FILE__)."/$filename");
    	return $result;
    }

    public function testHooversCustomizationUpgrade()
    {
        $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/hoovers_custom_functions.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/listviewdefs.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/mapping.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/vardefs.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/metadata/mergeviewdefs.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/metadata/searchdefs.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/metadata/display_config.php'));
        $this->assertTrue(file_exists('custom/modules/Connectors/metadata/connectors.php'));

        //Alright now let's call the code to upgrade the config
        require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
        $old_config = $config;
        $config = null;

        require_once('modules/UpgradeWizard/uw_utils.php');
        upgrade_connectors('sugarcrm.log');

        //Check that config.php was modified correctly
        require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/config.php');
        $this->assertNotEquals($old_config['properties']['hoovers_endpoint'], $config['properties']['hoovers_endpoint'], 'Assert that endpoint value has changed');
        $this->assertNotEquals($old_config['properties']['hoovers_wsdl'], $config['properties']['hoovers_wsdl'], 'Assert that wsdl value has changed');
        $this->assertEquals('http://hapi.hoovers.com/HooversAPI-33', $config['properties']['hoovers_endpoint'], 'Assert that endpoint is http://hapi.hoovers.com/HooversAPI-33');
        $this->assertEquals('http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl', $config['properties']['hoovers_wsdl'], 'Assert that endpoint is http://hapi.hoovers.com/HooversAPI-33/hooversAPI/hooversAPI.wsdl');

        //Check that vardefs.php was modified correctly
        require('custom/modules/Connectors/connectors/sources/ext/soap/hoovers/vardefs.php');
        $this->assertEquals('bal.specialtyCriteria.companyName', $dictionary['ext_soap_hoovers']['fields']['recname']['input'], "Assert that the input key for recname entry was changed to 'bal.specialtyCriteria.companyName'");

        $source_instance = ConnectorFactory::getInstance('ext_soap_hoovers');
//BEGIN SUGARCRM flav!=int ONLY
		$mock = $this->getMockFromWsdl(
          		dirname(__FILE__).'/hooversAPI.wsdl', 'HooversAPIMock'
        	);
        $mockClient = new HooversConnectorsMockClient($mock);
        $source_instance->getSource()->setClient($mockClient);
    	$mock->expects($this->once())
    		->method('GetCompanyDetail')
    		->will($this->returnValue($this->getResultData('gannett.php')));
//END SUGARCRM flav!=int ONLY
        $account = new Account();
        $account = $source_instance->fillBean(array('id'=>'2205698'), 'Accounts', $account);
        $this->assertEquals(preg_match('/^Gannett/i', $account->name), 1, "Assert that account name is like Gannett");


        //$account = new Account();
        $accounts = array();
        $accounts = $source_instance->fillBeans(array('name' => 'Gannett'), 'Accounts', $accounts);
        foreach($accounts as $count=>$account) {
                $this->assertEquals(preg_match('/^Gannett/i', $account->name), 1, "Assert that a bean has been filled with account name like Gannett");
                break;
        }
    }
}
<?php

require_once('modules/ModuleBuilder/MB/ModuleBuilder.php');

class Bug36845Test2 extends Sugar_PHPUnit_Framework_TestCase {

var $has_custom_unified_search_modules = false;	
var $module_dir = 'modules/clabc_test_Bug36845Test';
var $module = 'clabc_test_Bug36845Test';

public function setUp() 
{
	if(file_exists('cache/modules/unified_search_modules.php'))
	{
		$this->has_custom_unified_search_modules = true;
		copy('cache/modules/unified_search_modules.php', 'cache/modules/unified_search_modules.php.bak');
	}
	
	if(file_exists($this->module_dir))
	{
	   rmdir_recursive($this->module_dir);
	}
	
	mkdir_recursive($this->module_dir . '/metadata');
	
$the_string = <<<EOQ
<?php
\$module_name = "{$this->module}";
\$searchFields[\$module_name] = 
	array (
		'name' => array( 'query_type'=>'default'),
		'account_type'=> array('query_type'=>'default', 'options' => 'account_type_dom', 'template_var' => 'ACCOUNT_TYPE_OPTIONS'),
		'industry'=> array('query_type'=>'default', 'options' => 'industry_dom', 'template_var' => 'INDUSTRY_OPTIONS'),
		'annual_revenue'=> array('query_type'=>'default'),
		'address_street'=> array('query_type'=>'default','db_field'=>array('billing_address_street','shipping_address_street')),
		'address_city'=> array('query_type'=>'default','db_field'=>array('billing_address_city','shipping_address_city')),
		'address_state'=> array('query_type'=>'default','db_field'=>array('billing_address_state','shipping_address_state')),
		'address_postalcode'=> array('query_type'=>'default','db_field'=>array('billing_address_postalcode','shipping_address_postalcode')),
		'address_country'=> array('query_type'=>'default','db_field'=>array('billing_address_country','shipping_address_country')),
		'rating'=> array('query_type'=>'default'),
		'phone'=> array('query_type'=>'default','db_field'=>array('phone_office')),
		'email'=> array('query_type'=>'default','db_field'=>array('email1','email2')),
		'website'=> array('query_type'=>'default'),
		'ownership'=> array('query_type'=>'default'),
		'employees'=> array('query_type'=>'default'),
		'ticker_symbol'=> array('query_type'=>'default'),
		'current_user_only'=> array('query_type'=>'default','db_field'=>array('assigned_user_id'),'my_items'=>true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'),
		'assigned_user_id'=> array('query_type'=>'default'),
		'favorites_only' => array(
            'query_type'=>'format',
			'operator' => 'subquery',
			'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites 
			                    WHERE sugarfavorites.deleted=0 
			                        and sugarfavorites.module = \''.\$module_name.'\' 
			                        and sugarfavorites.assigned_user_id = \'{0}\'',
			'db_field'=>array('id')),
	);
?>
EOQ;

$fp = sugar_fopen($this->module_dir . '/metadata/SearchFields.php', "w");
fwrite( $fp, $the_string );
fclose( $fp );	
	
$table_name = strtolower($this->module);
$the_string = <<<EOQ
<?php
\$dictionary["{$this->module}"] = array(
	'table'=>"{$table_name}",
	'audited'=>true,
	'fields'=>array (
),
	'relationships'=>array (
),
	'optimistic_locking'=>true,
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef("{$this->module}","{$this->module}", array('basic','team_security','assignable','company'));
?>
EOQ;

$fp = sugar_fopen($this->module_dir . '/vardefs.php', "w");
fwrite( $fp, $the_string );
fclose( $fp );

if(file_exists('custom/modulebuilder/packages/clabc'))
{
	rmdir_recursive('custom/modulebuilder/packages/clabc');
}

mkdir_recursive('custom/modulebuilder/packages/clabc/modules/test_Bug36845Test');

$the_string = <<<EOQ
<?php
\$config = array (
  'team_security' => true,
  'assignable' => true,
  'acl' => true,
  'has_tab' => true,
  'studio' => true,
  'audit' => true,
  'templates' => 
  array (
    'basic' => 1,
    'company' => 1,
  ),
  'label' => 'Bug 36845Test',
  'importable' => false,
);
?>
EOQ;

$fp = sugar_fopen('custom/modulebuilder/packages/clabc/modules/test_Bug36845Test/config.php', "w");
fwrite( $fp, $the_string );
fclose( $fp );	

$the_string = <<<EOQ
<?php
    \$manifest = array (
         'acceptable_sugar_versions' => 
          array (
            
          ),
          'acceptable_sugar_flavors' =>
          array(
            'ENT'
          ),
          'readme'=>'',
          'key'=>'clabc',
          'author' => 'Collin Lee',
          'description' => '',
          'icon' => '',
          'is_uninstallable' => true,
          'name' => 'test',
          'published_date' => '2010-11-15 18:06:52',
          'type' => 'module',
          'version' => '1289844412',
          'remove_tables' => 'prompt',
          );
?>
EOQ;

$fp = sugar_fopen('custom/modulebuilder/packages/clabc/manifest.php', "w");
fwrite( $fp, $the_string );
fclose( $fp );	


}

public function tearDown()
{
	if(file_exists('cache/modules/unified_search_modules.php'))
	{
		unlink('cache/modules/unified_search_modules.php');
	}
	
	if($this->has_custom_unified_search_modules)
	{
		copy('cache/modules/unified_search_modules.php.bak', 'cache/modules/unified_search_modules.php');
	}
	
	if(file_exists($this->module_dir))
	{
	   rmdir_recursive($this->module_dir);
	}	
	
	rmdir_recursive('custom/modulebuilder/packages/clabc');
}

public function test_update_custom_vardefs()
{
    $this->assertTrue(file_exists("{$this->module_dir}/metadata/SearchFields.php"), 'Assert that we have a SearchFields.php file');
    $this->assertTrue(file_exists("{$this->module_dir}/vardefs.php"), 'Assert that we have a vardefs.php file');
    require_once('modules/UpgradeWizard/uw_utils.php');
    add_unified_search_to_custom_modules_vardefs();
    require($this->module_dir . '/vardefs.php');
    $this->assertTrue($dictionary['clabc_test_Bug36845Test']['unified_search'], 'Assert that the add_unified_search_to_custom_modules function worked');
}


public function test_update_custom_vardefs_without_searchfields()
{
	unlink("{$this->module_dir}/metadata/SearchFields.php");
    $this->assertTrue(!file_exists("{$this->module_dir}/metadata/SearchFields.php"), 'Assert that we have a SearchFields.php file');
    $this->assertTrue(file_exists("{$this->module_dir}/vardefs.php"), 'Assert that we have a vardefs.php file');
    require_once('modules/UpgradeWizard/uw_utils.php');
    add_unified_search_to_custom_modules_vardefs();
    require($this->module_dir . '/vardefs.php');
    $this->assertTrue(!isset($dictionary['clabc_test_Bug36845Test']['unified_search']), 'Assert that add_unified_search_to_custom_modules function worked');
}


}
?>
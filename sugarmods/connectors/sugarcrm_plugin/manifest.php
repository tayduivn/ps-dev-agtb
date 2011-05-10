<?php
$manifest = array(
  'acceptable_sugar_flavors' => array(
    'CE',
    'PRO',
	'ENT',
  ),
  'acceptable_sugar_versions' => array(
    '5.2.0',
  ),
  'is_uninstallable' => true,
  'name' => 'SugarCRM Connector',
  'description' => 'Connector to SugarCRM Proxy Server',
  'author' => 'Collin Lee',
  'published_date' => '2008/11/24',
  'version' => '1.0',
  'type' => 'module',
  'icon' => '',
);

$installdefs = array (
  'id' => 'ext_rest_sugarcrm',
  'connectors' => array (
    array (
      'connector' => '<basepath>/sugarcrm/source',
      'name' => 'ext_rest_sugarcrm',
    ),  
  ),
);

?>

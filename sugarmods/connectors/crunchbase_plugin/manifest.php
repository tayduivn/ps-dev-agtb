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
  'name' => 'Crunchase Connector',
  'description' => 'Connector to Crunchbase',
  'author' => 'Collin Lee',
  'published_date' => '2008/11/19',
  'version' => '1.0',
  'type' => 'module',
  'icon' => '',
);

$installdefs = array (
  'id' => 'ext_rest_crunchbase',
  'connectors' => array (
    array (
      'connector' => '<basepath>/crunchbase/source',
	'formatter' => '<basepath>/crunchbase/formatter',
      'name' => 'ext_rest_crunchbase',
    ),
  ),

);

?>

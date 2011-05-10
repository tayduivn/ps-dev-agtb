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
  'name' => 'Test Connector',
  'description' => 'Connector for testing purposes only',
  'author' => 'John Doe',
  'published_date' => '2008/12/12',
  'version' => '1.0',
  'type' => 'module',
  'icon' => '',
);

$installdefs = array (
  'id' => 'ext_rest_test',
  'connectors' => array (
    array (
      'connector' => '<basepath>/test/source',
      'formatter' => '<basepath>/test/formatter',
      'name' => 'ext_rest_test',
    ),
  ),

);

?>

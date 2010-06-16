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
  'name' => 'Zoominfo Connector',
  'description' => 'Connector to Zoominfo Person and Zoominfo Company',
  'author' => 'Collin Lee',
  'published_date' => '2008/11/19',
  'version' => '1.0',
  'type' => 'module',
  'icon' => '',
);

$installdefs = array (
  'id' => 'ext_rest_zoominfo',
  'connectors' => array (
    array (
      'connector' => '<basepath>/zoominfoperson/source',
      'name' => 'ext_rest_zoominfoperson',
    ),
    array (
      'connector' => '<basepath>/zoominfocompany/source',
      'name' => 'ext_rest_zoominfocompany',
    ),    
  ),
);

?>

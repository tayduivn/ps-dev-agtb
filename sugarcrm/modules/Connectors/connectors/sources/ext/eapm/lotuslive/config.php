<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$config = array (
  'name' => 'LotusLive&#169;',
  'eapm' => array(
    'enabled' => true,
    'only' => true,
  ),
  'order' => 14,
  'properties' => array (
      'oauth_consumer_key' => '',
      'oauth_consumer_secret' => '',
  ),
);
//BEGIN SUGARCRM flav=int ONLY
$config['properties']['oauth_consumer_key'] = '9399cf0ce6e4ca4d30d56a76b21da89';
$config['properties']['oauth_consumer_secret'] = '7704b27829c5715445e14637415b67c1';
//END SUGARCRM flav=int ONLY

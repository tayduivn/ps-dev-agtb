<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$config = array (
  'name' => 'LotusLive',
  'eapm' => array(
    'enabled' => true,
    'only' => true,
  ),
  'order' => 14,
  'properties' => array (
      'oauth_consumer_key' => 'test_app',
      'oauth_consumer_secret' => '87323at4aj6y8e9a0pa92w',
  ),
);

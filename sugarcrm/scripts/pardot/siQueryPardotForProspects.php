<?php
/* This is just a demonstration file */



require_once('pardotApi.class.php');
require('scripts/pardot/pardot_config.php');

$pardot = pardotApi::magic();

/*
 * Get the first group of 200
 */
$prospects = $pardot->getProspectsWhere(array('score_greater_than' => $pardot_config['min_score_to_sync']), 'mobile');

/*
 * You need this id to get the next batch
 */
$max_id = max(array_keys($prospects));

$lastResultCount = $pardot->getLastResultCount();
echo 'Harvested ' . count($prospects) . ' prospects out of ' . $lastResultCount . "\n";
echo join(', ', array_keys($prospects)) . "\n";

/*
 * Get the next group of 200
 */
$prospects = $pardot->getProspectsWhere(array('id_greater_than' => $max_id,
					      'score_greater_than' => 100), 'mobile');
echo 'Harvested ' . count($prospects) . ' prospects out of ' . $lastResultCount . "\n";
echo join(', ', array_keys($prospects)) . "\n";




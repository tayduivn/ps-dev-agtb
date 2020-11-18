<?php

$coreDefs = MetaDataFiles::loadSingleClientMetadata('view','planned-activities');
$coreDefs['dashlets'][0]['filter']['module'] = array('gtb_matches');
$coreDefs['tabs'][0]['link'] = 'gtb_matches_activities_1_meetings';
$coreDefs['tabs'][1]['link'] = 'gtb_matches_activities_1_calls';
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][0]['params']['link'] = 'gtb_matches_activities_1_meetings';
$coreDefs['custom_toolbar']['buttons'][0]['buttons'][1]['params']['link'] = 'gtb_matches_activities_1_calls';
$viewdefs['gtb_matches']['base']['view']['planned-activities'] = $coreDefs;

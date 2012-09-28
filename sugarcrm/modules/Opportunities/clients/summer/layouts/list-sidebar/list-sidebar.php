<?php

require_once('clients/summer/GenericLayout.php');
$layout = new GenericLayout();
$layout->push(array('view'=>'treemap'));
$layout->push(array('view'=>'funnel'));
$layout->push(array('view'=>'untouched'));
$layout->push(array('view'=>'leaderboard'));
$layout->push(array('view'=>'activitystream'));

$viewdefs['Opportunities']['summer']['layout']['list-sidebar'] = $layout->getLayout();

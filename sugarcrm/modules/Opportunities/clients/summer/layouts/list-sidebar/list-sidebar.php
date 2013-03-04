<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'treemap'));
$layout->push(array('view'=>'funnel'));
$layout->push(array('view'=>'leaderboard'));

$viewdefs['Opportunities']['summer']['layout']['list-sidebar'] = $layout->getLayout();

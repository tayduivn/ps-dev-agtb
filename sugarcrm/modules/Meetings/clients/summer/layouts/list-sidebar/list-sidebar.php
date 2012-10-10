<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push('top', array('view'=>'subnav'));
$layout->push(array('view'=>'agenda'));
$viewdefs['Meetings']['summer']['layout']['list-sidebar'] = $layout->getLayout();

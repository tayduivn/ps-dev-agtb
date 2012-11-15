<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'activitystream'));
$viewdefs['Contacts']['summer']['layout']['list-sidebar'] = $layout->getLayout();

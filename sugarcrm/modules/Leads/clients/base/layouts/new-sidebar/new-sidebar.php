<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view' => 'createhelp'));
$viewdefs['Leads']['base']['layout']['new-sidebar'] = $layout->getLayout();

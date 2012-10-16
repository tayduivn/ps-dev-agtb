<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view' => 'createhelp'));
$viewdefs['ProspectLists']['base']['layout']['new-sidebar'] = $layout->getLayout();

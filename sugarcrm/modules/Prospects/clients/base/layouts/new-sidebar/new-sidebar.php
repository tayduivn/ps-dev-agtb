<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view' => 'createhelp'));
$viewdefs['Prospects']['base']['layout']['new-sidebar'] = $layout->getLayout();

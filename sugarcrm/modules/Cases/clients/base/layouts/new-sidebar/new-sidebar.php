<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'createhelp'));
$viewdefs['Cases']['base']['layout']['new-sidebar'] = $layout->getLayout();

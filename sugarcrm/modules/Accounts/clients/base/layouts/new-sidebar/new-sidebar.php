<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'createhelp'));
$viewdefs['Accounts']['base']['layout']['new-sidebar'] = $layout->getLayout();

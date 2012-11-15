<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'createhelp'));
$viewdefs['Contacts']['summer']['layout']['new-sidebar'] = $layout->getLayout();

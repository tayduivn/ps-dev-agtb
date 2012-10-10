<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'createhelp'));
$viewdefs['Accounts']['summer']['layout']['new-sidebar'] = $layout->getLayout();

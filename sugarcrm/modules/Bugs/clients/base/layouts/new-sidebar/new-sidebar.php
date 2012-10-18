<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'createhelp'));
$viewdefs['Bugs']['base']['layout']['new-sidebar'] = $layout->getLayout();

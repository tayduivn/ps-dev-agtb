<?php
$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('layout' => 'selection-list', 'context' => array('module' => 'Documents')));
$viewdefs['Emails']['base']['layout']['compose-documents'] = $layout->getLayout();

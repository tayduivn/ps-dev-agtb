<?php

$layout = MetaDataManager::getLayout('SideBarLayout');

$layout->push('main', array('view' => 'sg-headerpane'));
$layout->push('main', array('view' => 'field'));

$viewdefs['Styleguide']['base']['layout']['field'] = $layout->getLayout();
$viewdefs['Styleguide']['base']['layout']['field']['type'] = 'field';

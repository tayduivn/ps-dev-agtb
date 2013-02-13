<?php
$layout = MetaDataManager::getLayout('SideBarLayout');

$layout->push('main', array('view'=>'compose-templates-headerpane'));
$layout->push('main', array('view'=>'compose-templates'));
$layout->push('main', array('view'=>'list-bottom'));

$viewdefs['EmailTemplates']['base']['layout']['compose-templates'] = $layout->getLayout();

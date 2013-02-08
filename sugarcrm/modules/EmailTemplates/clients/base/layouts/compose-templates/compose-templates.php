<?php
$layout = MetaDataManager::getLayout('SideBarLayout');

$layout->push('main', array('view'=>'compose-templates-headerpane'));
$layout->push('main', array('view'=>'compose-templates'));

$viewdefs['EmailTemplates']['base']['layout']['compose-templates'] = $layout->getLayout();

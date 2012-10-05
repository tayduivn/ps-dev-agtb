<?php

$layout = new SideBarLayout();
$layout->push('main', array('view' => 'record'));
$layout->push('side', array('layout' => 'new-sidebar'));
$viewdefs['summer']['layout']['newrecord'] = $layout->getLayout();

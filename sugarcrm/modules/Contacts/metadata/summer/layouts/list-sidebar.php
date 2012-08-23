<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main',array('view'=>'activitystream'));
$viewdefs['Contacts']['summer']['layout']['list-sidebar'] = $layout->getLayout();

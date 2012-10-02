<?php

require_once('clients/summer/TabbedLayout.php');
$layout = new TabbedLayout();
$layout->push('main', array('view'=>'activitystream', 'label'=>'Activity Stream'));
$viewdefs['summer']['layout']['tabbed-layout'] = $layout->getLayout();
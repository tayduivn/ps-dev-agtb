<?php

$layout = new GenericLayout();
$layout->push(array('view'=>'activitystream'));
$viewdefs['Contacts']['summer']['layout']['list-sidebar'] = $layout->getLayout();

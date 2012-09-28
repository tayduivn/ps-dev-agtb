<?php
require('clients/summer/SideBarLayout.php');

$layout = new GenericLayout();
$layout->push(array('view' => 'createhelp'));
$viewdefs['Accounts']['summer']['layout']['new-sidebar'] = $layout->getLayout();

<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$viewdefs['Contacts']['summer']['layout']['list-sidebar'] = $layout->getLayout();

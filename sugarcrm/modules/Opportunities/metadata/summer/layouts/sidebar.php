<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();

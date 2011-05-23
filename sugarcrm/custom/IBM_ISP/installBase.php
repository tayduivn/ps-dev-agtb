<?php
$isp_page = 'installBase';
$tabIndex = 2;

$url = IBMHelper::getISPTargetURL($isp_page, $smarty->_tpl_vars['fields']);

$tab_content = IBMHelper::getISPTabContent('Accounts', $isp_page, $tabIndex, $url, 800);

echo $tab_content;

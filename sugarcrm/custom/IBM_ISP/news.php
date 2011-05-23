<?php
$isp_page = 'news';
$tabIndex = 4;

$url = IBMHelper::getISPTargetURL($isp_page, $smarty->_tpl_vars['fields']);

$tab_content = IBMHelper::getISPTabContent('Accounts', $isp_page, $tabIndex, $url, 1000);

echo $tab_content;

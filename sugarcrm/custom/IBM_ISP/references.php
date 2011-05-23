<?php
$isp_page = 'references';
$tabIndex = 6;

$url = IBMHelper::getISPTargetURL($isp_page, $smarty->_tpl_vars['fields']);

$tab_content = IBMHelper::getISPTabContent('Accounts', $isp_page, $tabIndex, $url, 2000);

echo $tab_content;

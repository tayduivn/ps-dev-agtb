<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//Begin Sugar Interal customizations
$beanList['SugarUpdates'] = 'SugarUpdate';
$beanFiles['SugarUpdate'] = 'modules/SugarUpdates/SugarUpdate.php';
$modInvisList[] = 'SugarUpdates';

//$beanList['DownloadKeys'] = 'DownloadKey';
//$beanFiles['DownloadKey'] = 'modules/DownloadKeys/DownloadKey.php';
//$modInvisList[] = 'DownloadKeys';

//$beanList['Orders'] = 'Order';
//$beanFiles['Order'] = 'modules/Orders/Order.php';
//$modInvisList[] = 'Orders';

//$beanList['PortalUsers'] = 'PortalUser';
//$beanFiles['PortalUser'] = 'modules/PortalUsers/PortalUser.php';
//$modInvisList[] = 'PortalUsers';

$beanList['SugarInstallations'] = 'SugarInstallation';
$beanFiles['SugarInstallation'] = 'modules/SugarInstallations/SugarInstallation.php';
$moduleList[] = 'SugarInstallations';

$beanList['Subscriptions'] = 'Subscription';
$beanFiles['Subscription'] = 'modules/Subscriptions/Subscription.php';
$moduleList[] = 'Subscriptions';

$modInvisList[] = 'DistGroups';
$beanList['DistGroups'] = 'DistGroup';
$beanFiles['DistGroup'] = 'modules/DistGroups/DistGroup.php';


// SADEK 03/24/2010: Moved M2 Customizations to include/modules.php to include/modules_override.php
$beanList['LeadAccounts']   = 'LeadAccount';
$beanFiles['LeadAccount']   = 'modules/LeadAccounts/LeadAccount.php';
$modListGlobalSearchExceptions['LeadAccounts'] = 'LeadAccounts';
$modInvisList[] = 'LeadAccounts';

$beanList['LeadContacts']   = 'LeadContact';
$beanFiles['LeadContact']   = 'modules/LeadContacts/LeadContact.php';
$modListGlobalSearchExceptions['LeadContacts'] = 'LeadContacts';
$modInvisList[] = 'LeadContacts';

$beanList['Interactions']   = 'Interaction';
$beanFiles['Interaction']   = 'modules/Interactions/Interaction.php';
$modListGlobalSearchExceptions['Interactions'] = 'Interaction';
$modInvisList[] = 'Interactions';

$beanList['Touchpoints']    = 'Touchpoint';
$beanFiles['Touchpoint']    = 'modules/Touchpoints/Touchpoint.php';
$modListGlobalSearchExceptions['Touchpoints']  = 'Touchpoints';
$modInvisList[] = 'Touchpoints';

$beanList['Score'] = 'Score';
$beanFiles['Score'] = 'modules/Score/Score.php';
$modInvisList[] = 'Score';

foreach($moduleList as $index => $moduleListName){
	if($moduleListName == 'Feeds'){
		unset($moduleList[$index]);
	}
}

if(isset($beanList['Feeds'])){
	unset($beanList['Feeds']);
}

if(isset($beanFiles['Feed'])){
	unset($beanFiles['Feed']);
}
// SADEK: END

?>

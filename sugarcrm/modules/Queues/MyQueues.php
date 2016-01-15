<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
//FILE SUGARCRM flav=int ONLY
global $current_user;
require_once('modules/Queues/Queue.php');
$focus = new Queue();
$focus->disable_row_level_security = true;
$focus->getQueueFromOwnerId($current_user->id, true);
//$items = $focus->getQueueItemsRecursively();
$items = $focus->getQueueItems();

global $theme;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Queues');




require_once('include/DetailView/DetailView.php');
?>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td nowrap>
			<h3><?php echo SugarThemeRegistry::current()->getImage("h3Arrow", 'border="0"', 11, 11, ".gif", $current_module_strings['LBL_HOME_TITLE']) ?>
				&nbsp;<?php echo $current_module_strings['LBL_HOME_TITLE']; echo '('.count($items).') Items'; ?></h3>
		</td>
		<td width='100%'>
			<IMG height='1' width='1' src='<?php echo getJSPath("include/images/blank.gif"); ?>' alt=''>
		</td>
	</tr>
</table>


<table width="100%" cellpadding="0" cellspacing="0" border="0" style='padding-bottom:5px'>
	<tr>
		<td nowrap>
			<form name='GetSome' id='GetSome' action='index.php' method='GET'>
				<input type="hidden" name="module" value="Queues">
				<input type="hidden" name="action" value="GetSome">
				<input title="<?php echo $current_module_strings['LBL_GET_SOME']; ?>" class="button" onclick="this.form.action.value='GetSome';" type="submit" name="button" value="   <?php echo $current_module_strings['LBL_GET_SOME']; ?>  " >
			</form>
		</td>
	</tr>
</table>


<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view">
	<tr height="20">
		<td scope="col" width="5%"  align="left"><slot>
			&nbsp;</slot></td>
		<td scope="col" width="40%"  align="left"><slot>
			<?php echo $current_module_strings['LBL_BEAN_NAME'];?></slot></td>
		<td scope="col" width="25%"  align="left"><slot>
			<?php echo $current_module_strings['LBL_INSTANT_ACTION'];?></slot></td>
		<td scope="col" width="5%"  align="right"><slot>
			&nbsp;</slot></td>
		<td scope="col" width="25%"  align="right" NOWRAP><slot>
			<?php echo $current_module_strings['LBL_ASSOC_EVENT'];?></slot></td>
	  </tr>
<?php
global $odd_bg;
global $even_bg;
global $hilite_bg;
$oddRow = true;

if(!empty($items)) {
	foreach($items as $k => $bean) {
		$taskLink = '';
		$ahref = '';
		$beanNameLink = '';
		$taskIcon = '';
		$instantAction = '';

		// handle bean type of Email
		if($bean->object_name == 'Email') {
			if(!empty($bean->reply_to_email)) {
				$reply_to = $bean->reply_to_email;
			} elseif(!empty($bean->from_addr)) {
				$reply_to = $bean->from_addr;
			} else {
				$reply_to = '';
			}


			// beanNameLink
			// beanNameLink
			$bname = $bean->name;
			if(strlen($bname) > 20) {
				$bname = substr($bname, 0, 20).'...';
			}
			$beanNameLink = "<a href='index.php?module=".$bean->module_dir."&action=DetailView&record=".$bean->id."'>".$bname."</a>";
			$queueIcon = $ahref.SugarThemeRegistry::current()->getImage("Emails", "", null, null, ".gif", $mod_strings['LBL_EMAILS'])."</a>";
			$instantAction = "<a href=\"index.php?module=Emails&action=EditView&type=out&inbound_email_id=".$bean->id."&return_module=Home&return_action=index&to_email_addrs=".$reply_to."&email_name=".str_replace(' ','_','RE: '.trim($bean->name))."\">
							".$current_module_strings['LBL_REPLY']."</a>";


			// handle Inbounds
			if($bean->type == 'inbound') {

				if($cases = $bean->get_cases()) {
					$bean->case_id = $cases[0]->id;
					$bean->case_name = $cases[0]->name;
				}

				if(!empty($bean->case_id) && !empty($bean->case_name)) {
					$ahref = "<a href=\"index.php?module=Cases&action=DetailView&record=".$bean->case_id."&contact_id=".$bean->contact_id."&case_name=".str_replace(' ','_',trim($bean->case_name))."\">";
					$taskIcon = $ahref.SugarThemeRegistry::current()->getImage("Cases", "", null, null, ".gif", $mod_strings['LBL_CASES'])."</a>";
					$taskLink = $ahref.trim($bean->case_name)."</a>";
				} else {
					$ahref = "<a href=\"index.php?module=Cases&action=EditView&inbound_email_id=".$bean->id."&contact_id=".$bean->contact_id."&case_name=".str_replace(' ','_',trim($bean->name))."\">";
					$taskIcon = $ahref.SugarThemeRegistry::current()->getImage("Cases", "", null, null, ".gif", $mod_strings['LBL_CASES'])."</a>";
					$taskLink = $ahref.$current_module_strings['LBL_CREATE_NEW_CASE']."</a>";
				}
			} else {
				// catchall associated task
				if(empty($taskLink)) {
					$taskLink  = '<form name="MyQueueForm" action="index.php" method="GET">';
					$taskLink .= '<input type="hidden" name="action" value="EditView">';
					$taskLink .= '<select name="module" onChange="submit();">';
					$taskLink .= '<option value="">'.$current_module_strings['DOM_LBL_NONE'].'</option>';
					$taskLink .= get_select_options_with_id($current_module_strings['DOM_ACTION_TYPE'], '');
					$taskLink .= '</select></form>';
				}

			}
		}



		$xtpl = new XTemplate('modules/Queues/MyQueues.html');

		$xtpl->assign('QUEUE_ITEM_ICON', $queueIcon);
		$xtpl->assign('BEAN_NAME_LINK', $beanNameLink);
		$xtpl->assign('INSTANT_ACTION', $instantAction);
		$xtpl->assign('ASSOC_TASK', $taskLink);
		$xtpl->assign('BEAN_ICON', $taskIcon);
		$xtpl->assign('BEAN_ID', $bean->id);

		$xtpl->assign('BG_HILITE', $hilite_bg);
		if($oddRow) {
			$ROW_COLOR = 'oddListRow';
			$BG_COLOR =  $odd_bg;
		} else {
			$ROW_COLOR = 'evenListRow';
			$BG_COLOR =  $even_bg;
		}
		$oddRow = !$oddRow;

		$xtpl->assign('ROW_COLOR', $ROW_COLOR);
		$xtpl->assign('BG_COLOR', $BG_COLOR);

		$xtpl->parse("main");
		$xtpl->out("main");
	}
//	_ppd($typeloop);
} // end if($items)
?>

</table>

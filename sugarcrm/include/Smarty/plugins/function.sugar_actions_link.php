<?php

/*

Modification information for LGPL compliance

/**
 * smarty_function_sugar_actions_link
 * This is the constructor for the Smarty plugin.
 *
 * @param $params The runtime Smarty key/value arguments
 * @param $smarty The reference to the Smarty object used in this invocation
 */
function smarty_function_sugar_actions_link($params, &$smarty)
{
   if(empty($params['module'])) {
   	  $smarty->trigger_error("sugar_button: missing required param (module)");
   } else if(empty($params['id'])) {
   	  $smarty->trigger_error("sugar_button: missing required param (id)");
   } else if(empty($params['view'])) {
   	  $smarty->trigger_error("sugar_button: missing required param (view)");
   }

   $type = $params['id'];
   $location = (empty($params['location'])) ? "" : "_".$params['location'];

   if(!is_array($type)) {
   	  $module = $params['module'];
   	  $view = $params['view'];
   	  switch(strtoupper($type)) {
			case "SEARCH":
			return '<input tabindex="2" title="{$APP.LBL_SEARCH_BUTTON_TITLE}" accessKey="{$APP.LBL_SEARCH_BUTTON_KEY}" onclick="SUGAR.savedViews.setChooser(); SUGAR.ajaxUI.submitForm(this.form);" class="button" type="button" name="button" value="{$APP.LBL_SEARCH_BUTTON_LABEL}" id="search_form_submit"/>&nbsp;';
			break;

			case "CANCEL":
			$cancelButton  = '{if !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($smarty.request.return_id))}';
			$cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=DetailView&module={$smarty.request.return_module}&record={$smarty.request.return_id}\'); return false;" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" type="button" id="'.$type.$location.'"> ';
			$cancelButton .= '{elseif !empty($smarty.request.return_action) && ($smarty.request.return_action == "DetailView" && !empty($fields.id.value))}';
			$cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=DetailView&module={$smarty.request.return_module}&record={$fields.id.value}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';
			$cancelButton .= '{elseif empty($smarty.request.return_action) || empty($smarty.request.return_id) && !empty($fields.id.value)}';
			$cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=index&module='.$module.'\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';
			$cancelButton .= '{else}';
			$cancelButton .= '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="SUGAR.ajaxUI.loadContent(\'index.php?action=index&module={$smarty.request.return_module}&record={$smarty.request.return_id}\'); return false;" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="'.$type.$location.'"> ';
			$cancelButton .= '{/if}';
			return $cancelButton;
			break;

			case "DELETE":
			return '{if $bean->aclAccess("delete")}<li><a title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" onclick="document.getElementById(\'form\').return_module.value=\'' . $module . '\'; document.getElementById(\'form\').return_action.value=\'ListView\'; document.getElementById(\'form\').action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" name="Delete" id="delete_button">{$APP.LBL_DELETE_BUTTON_LABEL}</a></li>{/if} ';
			break;

			case "DUPLICATE":
			return '{if $bean->aclAccess("edit")}<li><a title="{$APP.LBL_DUPLICATE_BUTTON_TITLE}" accessKey="{$APP.LBL_DUPLICATE_BUTTON_KEY}" onclick="document.getElementById(\'form\').return_module.value=\''. $module . '\'; document.getElementById(\'form\').return_action.value=\'DetailView\'; document.getElementById(\'form\').isDuplicate.value=true; document.getElementById(\'form\').action.value=\'' . $view . '\'; document.getElementById(\'form\').return_id.value=\'{$id}\';SUGAR.ajaxUI.submitForm(document.getElementById(\'form\'));" name="Duplicate" id="duplicate_button">{$APP.LBL_DUPLICATE_BUTTON_LABEL}</a></li>{/if} ';
			break;

			case "EDIT";
			return '{if $bean->aclAccess("edit")}<li><a title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" onclick="document.getElementById(\'form\').return_module.value=\'' . $module . '\'; document.getElementById(\'form\').return_action.value=\'DetailView\'; document.getElementById(\'form\').return_id.value=\'{$id}\'; document.getElementById(\'form\').action.value=\'EditView\';SUGAR.ajaxUI.submitForm(document.getElementById(\'form\'));" name="Edit" id="edit_button">{$APP.LBL_EDIT_BUTTON_LABEL}</a></li>{/if} ';
			break;

			case "FIND_DUPLICATES":
			return '{if $bean->aclAccess("edit") && $bean->aclAccess("delete")}<li><a title="{$APP.LBL_DUP_MERGE}" accessKey="M" onclick="document.getElementById(\'form\').return_module.value=\'' . $module . '\'; document.getElementById(\'form\').return_action.value=\'DetailView\'; document.getElementById(\'form\').return_id.value=\'{$id}\'; document.getElementById(\'form\').action.value=\'Step1\'; document.getElementById(\'form\').module.value=\'MergeRecords\';SUGAR.ajaxUI.submitForm(document.getElementById(\'form\'));" name="Merge"  id="merge_duplicate_button">{$APP.LBL_DUP_MERGE}</a></li>{/if} ';
			break;

			case "SAVE":
				$view = ($_REQUEST['action'] == 'EditView') ? 'EditView' : (($view == 'EditView') ? 'EditView' : $view);
				return '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="{if $isDuplicate}this.form.return_id.value=\'\'; {/if}this.form.action.value=\'Save\'; if(check_form(\'' . $view . '\'))SUGAR.ajaxUI.submitForm(this.form);return false;" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" id="'.$type.$location.'">{/if} ';
			break;

			case "SUBPANELSAVE":
                if($view == 'QuickCreate' || (isset($_REQUEST['target_action']) && strtolower($_REQUEST['target_action'])) == 'quickcreate') $view =  "form_SubpanelQuickCreate_{$module}";
                return '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="disableOnUnloadEditView();this.form.action.value=\'Save\';if(check_form(\''.$view.'\'))return SUGAR.subpanelUtils.inlineSave(this.form.id, \'' . $params['module'] . '_subpanel_save_button\');return false;" type="submit" name="' . $params['module'] . '_subpanel_save_button" id="' . $params['module'] . '_subpanel_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';
			case "SUBPANELCANCEL":
				return '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="return SUGAR.subpanelUtils.cancelCreate(\'' . $params['module'] . '_subpanel_cancel_button\');return false;" type="submit" name="' . $params['module'] . '_subpanel_cancel_button" id="' . $params['module'] . '_subpanel_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';
		    case "SUBPANELFULLFORM":
				$html = '<input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" accessKey="{$APP.LBL_FULL_FORM_BUTTON_KEY}" class="button" onclick="disableOnUnloadEditView(this.form); this.form.return_action.value=\'DetailView\'; this.form.action.value=\'EditView\'; if(typeof(this.form.to_pdf)!=\'undefined\') this.form.to_pdf.value=\'0\';" type="submit" name="' . $params['module'] . '_subpanel_full_form_button" id="' . $params['module'] . '_subpanel_full_form_button" value="{$APP.LBL_FULL_FORM_BUTTON_LABEL}"> ';
				$html .= '<input type="hidden" name="full_form" value="full_form">';
		        return $html;
			case "DCMENUCANCEL":
				return '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="javascript:lastLoadedMenu=undefined;DCMenu.closeOverlay();return false;" type="submit" name="' . $params['module'] . '_dcmenu_cancel_button" id="' . $params['module'] . '_dcmenu_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';
		   
			case "DCMENUSAVE":
                            if ($view == 'QuickCreate') {
                                $view = "form_DCQuickCreate_{$module}";
                            } else if ($view == 'EditView') {
                                $view = "form_DCEditView_{$module}";
                            }
				return '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="this.form.action.value=\'Save\';if(check_form(\''.$view.'\'))return DCMenu.save(this.form.id, \'' . $params['module'] . '_subpanel_save_button\');return false;" type="submit" name="' . $params['module'] . '_dcmenu_save_button" id="' . $params['module'] . '_dcmenu_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';
			case "DCMENUFULLFORM":
                $html = '<input title="{$APP.LBL_FULL_FORM_BUTTON_TITLE}" accessKey="{$APP.LBL_FULL_FORM_BUTTON_KEY}" class="button" onclick="disableOnUnloadEditView(this.form); this.form.return_action.value=\'DetailView\'; this.form.action.value=\'EditView\'; this.form.return_module.value=\'' . $params['module'] . '\';this.form.return_id.value=this.form.record.value;if(typeof(this.form.to_pdf)!=\'undefined\') this.form.to_pdf.value=\'0\';SUGAR.ajaxUI.submitForm(this.form,null,true);DCMenu.closeOverlay();" type="button" name="' . $params['module'] . '_subpanel_full_form_button" id="' . $params['module'] . '_subpanel_full_form_button" value="{$APP.LBL_FULL_FORM_BUTTON_LABEL}"> ';
				$html .= '<input type="hidden" name="full_form" value="full_form">';
		        return $html;	
			case "POPUPSAVE":
				$view = $view == 'QuickCreate' ? "form_QuickCreate_{$module}" : $view;
				return '{if $bean->aclAccess("save")}<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" '
					 . 'class="button primary" onclick="this.form.action.value=\'Popup\';' 
					 . 'return check_form(\''.$view.'\')" type="submit" name="' . $params['module'] 
					 . '_popupcreate_save_button" id="' . $params['module'] 
					 . '_popupcreate_save_button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">{/if} ';
			case "POPUPCANCEL":
				return '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" ' 
					 . 'class="button" onclick="toggleDisplay(\'addform\');return false;" ' 
					 . 'name="' . $params['module'] . '_popup_cancel_button" type="submit"' 
					 . 'id="' . $params['module'] . '_popup_cancel_button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> ';
		    
			case "AUDIT":
	            $popup_request_data = array(
			        'call_back_function' => 'set_return',
			        'form_name' => 'EditView',
			        'field_to_name_array' => array(),
			    );
	            $json = getJSONobj();

	            require_once('include/SugarFields/Parsers/MetaParser.php');
	            $encoded_popup_request_data = MetaParser::parseDelimiters($json->encode($popup_request_data));
	 			$audit_link = '<li><a id="btn_view_change_log" title="{$APP.LNK_VIEW_CHANGE_LOG}" onclick=\'open_popup("Audit", "600", "400", "&record={$fields.id.value}&module_name=' . $params['module'] . '", true, false, ' . $encoded_popup_request_data . '); return false;\'>{$APP.LNK_VIEW_CHANGE_LOG}</a></li>';
				$view = '{if $bean->aclAccess("detail")}{if !empty($fields.id.value) && $isAuditEnabled}'.$audit_link.'{/if}{/if}';
				return $view;

			//BEGIN SUGARCRM flav=pro || flav=sales ONLY
			//Button for the Connector intergration wizard
			case "CONNECTOR":
				require_once('include/connectors/utils/ConnectorUtils.php');
				require_once('include/connectors/sources/SourceFactory.php');
				$modules_sources = ConnectorUtils::getDisplayConfig();
				if(!is_null($modules_sources) && !empty($modules_sources))
				{
					foreach($modules_sources as $mod=>$entry) {
					    if($mod == $module && !empty($entry)) {
					    	foreach($entry as $source_id) {
					    		$source = SourceFactory::getSource($source_id);
					    		if($source->isEnabledInWizard()) {
					    			return '<input title="{$APP.LBL_MERGE_CONNECTORS}" accessKey="{$APP.LBL_MERGE_CONNECTORS_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=Connectors&action=Step1&record={$fields.id.value}&merge_module={$module}\'" name="merge_connector" value="{$APP.LBL_MERGE_CONNECTORS}">';
					    		}
					    	}
					    }
					}
				}
				return '';

			//END SUGARCRM flav=pro || flav=sales ONLY

   	  } //switch

   } else if(is_array($type) && isset($type['customCode'])) {
   	  return $type['customCode'];
   }

}

?>

<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */
// BEGIN SUGAR INTERNAL CUSTOMIZATION - GIVE DOUG ACCESS TO THE SCORING PAGE
if(!is_admin($GLOBALS['current_user']) && $GLOBALS['current_user']->user_name != 'dribback' && $GLOBALS['current_user']->user_name != 'rmeeker' && $GLOBALS['current_user']->user_name != 'charrick'){
	sugar_die('You do not have access to this page');
}
// END SUGAR INTERNAL CUSTOMIZATION - GIVE DOUG ACCESS TO THE SCORING PAGE

require_once('modules/Score/Score.php');
global $db;

$scoreModules = Score::getScoreModuleData();

// Nuke the rule class cache, we want to regenerate this anytime we go into the config section
if ( file_exists('cache/modules/Score/RuleClassList.php') ) {
	unlink('cache/modules/Score/RuleClassList.php');
}

$hasOneActiveModule = false;
$adminData = array();
$configHTML = array();
$addHTML = array();
$dirtyList = array();
foreach ( $scoreModules as $module => $scoreModuleData ) {
	// Fetch and save all of the necessary data
	$dirtyList[$module] = array();
	$moduleData = array();
	if ( isset($GLOBALS['app_list_strings']['moduleList'][$module]) ) {
		$moduleData['label'] = $GLOBALS['app_list_strings']['moduleList'][$module];
	} else {
		$moduleData['label'] = $module;
	}
    if ( !empty($scoreModuleData['parentModule']) ) {
        if ( isset($GLOBALS['app_list_strings']['moduleList'][$scoreModuleData['parentModule']]) ) {
            $moduleData['parentLabel'] = $GLOBALS['app_list_strings']['moduleList'][$scoreModuleData['parentModule']];
        } else {
            $moduleData['parentLabel'] = $parentModule;
        }
        
    }
	$moduleData['classes'] = Score::getRuleClassesForModule($module);
	$moduleData['configs'] = Score::getModuleConfigs($module);

	$supportedTypes = array();
	$specialRules = array();
	foreach ( $moduleData['classes'] as $className => $ruleClass ) {
		foreach ( $ruleClass->supportedFieldTypes as $type ) {
			$supportedTypes[$type] = $className;
		}
		if ( $ruleClass->isSpecialField == true) {
			$specialRules[] = $className;
		}
		$moduleData['addHTML'][$className] = $ruleClass->renderAdd($module);
	}

	$moduleClass = loadBean($module);

	$addFields = array();
	// Put the "special" rules at the top of the list
	foreach ( $specialRules as $ruleClass ) {
			$curr = array();
			$curr['ruleName'] = $ruleClass;
			if ( isset($mod_strings['LBL_ADD_'.$ruleClass]) ) {
				$curr['label'] = $mod_strings['LBL_ADD_'.$ruleClass];
			} else {
				$curr['label'] = $ruleClass;
			}
			$addFields['_SPECIAL_'.$ruleClass] = $curr;
	}

    // Handle the deletes early, so they aren't removed from the list of possible fields
    if ( !empty($_REQUEST['deleteConfig']) && isset($moduleData['configs']['rules'][$_REQUEST['deleteConfig']]) ) {
        unset($moduleData['configs']['rules'][$_REQUEST['deleteConfig']]);
        $dirtyList[$module][$_REQUEST['deleteConfig']] = 1;
    }

	// Get the list of fields already in use, so we can filter them out
	$usedFields = array();
	foreach ( $moduleData['configs']['rules'] as $prefix => $config ) {
		if ( ! $moduleData['classes'][$config['ruleClass']]->isSpecialField && !empty($config['field']) ) {
			$usedFields[$config['field']] = true;
		}
	}


	// Figure out the list of available fields for each module
	foreach ( $moduleClass->field_defs as $fieldName => $fieldData ) {
		if ( isset($usedFields[$fieldName]) ) {
			// We already have a rule for this field, skip it
			continue;
		}
		if ( isset($supportedTypes[$fieldData['type']]) ) {
			$curr = array();
			$curr['ruleName'] = $supportedTypes[$fieldData['type']];
			if ( isset($fieldData['vname']) ) {
				$curr['label'] = rtrim(translate($fieldData['vname'],$module),': ');
			} else {
				$curr['label'] = $fieldName;
			}
			$addFields[$fieldName] = $curr;
		}
	}
	$moduleData['addFields'] = $addFields;

	if ( isset($_REQUEST['saveScoreConfigs']) && $_REQUEST['saveScoreConfigs'] == 'true' ) {
		// We are saving, save the per-module configuration
		if ( isset($_REQUEST[$module.'_enabled']) && $_REQUEST[$module.'_enabled'] == 'true' ) {
			$moduleData['configs']['enabled'] = true;
		} else {
			$moduleData['configs']['enabled'] = false;
        }

        if ( isset($_REQUEST[$module.'_apply_mult']) ) {
            $moduleData['configs']['apply_mult'] = $_REQUEST[$module.'_apply_mult'];
        } else {
            $moduleData['configs']['apply_mult'] = 'record';
        }

		// Then go through each of the configs and save them, use their returned configs for this page
		foreach ( $moduleData['configs']['rules'] as $i => $config ) {
			$oldmd5 = md5(serialize($config));
			$moduleData['configs']['rules'][$i] = $moduleData['classes'][$config['ruleClass']]->saveConfig($config,$module,$_REQUEST);
			$newmd5 = md5(serialize($moduleData['configs']['rules'][$i]));
			if ( $oldmd5 != $newmd5 ) {
				$dirtyList[$module][$i] = 1;
			}
		}

		if ( !empty($_REQUEST['add'][$module]) ) {
			// They wanted to add a new rule
			$prefix = "C".create_guid_section(4);
			$className = $moduleData['addFields'][$_REQUEST['add'][$module]]['ruleName'];
			unset($moduleData['addFields'][$_REQUEST['add'][$module]]);
			// We're doing crazy array merging here so that the new element appears at the top of the list, sorted hashes can be weird
			$newConfig = $moduleData['classes'][$className]->addConfig($prefix,$module,$_REQUEST);
            // Have to go about this a round about way because array_merge would turn all numeric prefixes of new rows into array offset indexes and mess the whole game up
            $oldRules = $moduleData['configs']['rules'];
            $newRules[$prefix] = $newConfig;
            if ( is_array($oldRules) ) {
                foreach ( $oldRules as $key => $rule ) {
                    $newRules[(string)$key] = $rule;
                }
            }
			$moduleData['configs']['rules'] = $newRules;
			$dirtyList[$module]['_ALL_'] = 1;
		}

		Score::saveModuleConfigs($module,$moduleData['configs']);
		$moduleData['configs'] = Score::getModuleConfigs($module);
	}

	foreach ( $moduleData['configs']['rules'] as $configId => $config ) {
		$moduleData['configHTML'][$configId] = $moduleData['classes'][$config['ruleClass']]->render($module,$config);
	}

	if ( $moduleData['configs']['enabled'] == 'true' || $moduleData['configs']['enabled'] == true ) {
		$hasOneActiveModule = true;
	}

	$adminData[$module] = $moduleData;
}
// Now run through the dirty list and mark rows as dirty in the database
/*
// No longer used for SQL based scoring
foreach ( $dirtyList as $module => $prefixList ) {
	if ( isset($prefixList['_ALL_']) ) {
		Score::markDirty('source_rule',$module);
	} else {
		foreach ( $prefixList as $prefix => $ignore ) {
			Score::markDirty('source_rule',$module,$prefix);
		}
	}
}
*/

// If we have at least one active scoring module, set up a scheduler (or two)
require_once('modules/Schedulers/Scheduler.php');
$sched = new Scheduler();
$sched->retrieve_by_string_fields(array('job'=>'function::rescoreTotal'));
if ( empty($sched->id) ) {
	// Need to setup the scheduler entry for the incremental rescoring
	$sched = new Scheduler();
	$sched->name = 'Rescore all records';
	$sched->job = 'function::rescoreTotal';
	$sched->date_time_start = '2001-01-01 01:01:01';
	$sched->job_interval = rand(1,58).'::'.rand(1,4).'::*::*::*';
	$sched->status = 'Active';
	$sched->catch_up = 0;
	$sched->save();
}

// Now time to display the settings, for real
$sugar_smarty	= new Sugar_Smarty();
$sugar_smarty->assign('mod', $mod_strings);
$sugar_smarty->assign('app', $app_strings);
$sugar_smarty->assign('adminData', $adminData);
$sugar_smarty->assign('image_path', $GLOBALS['image_path']);
if ( ! isset($_REQUEST['active_tab']) ) {
	$sugar_smarty->assign('active_tab','LeadContacts');
} else {
	$sugar_smarty->assign('active_tab',$_REQUEST['active_tab']);
}

echo get_module_title($mod_strings['LBL_MODULE_TITLE'], $mod_strings['LBL_ADMIN_SETTINGS'], true);
$sugar_smarty->display('modules/Score/AdminSettings.tpl');

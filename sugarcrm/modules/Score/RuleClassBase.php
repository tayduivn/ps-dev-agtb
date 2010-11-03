<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 */
require_once('include/Sugar_Smarty.php');

class RuleClassBase {
	public $allowedModules = array();
	public $ruleName = 'BASE_RULE';
	public $supportedFieldTypes = array();
	public $isSpecialField = false;
	public $runNightly = false;

	// Override in your class, be sure to call the parents, they will setup a lot of handy stuff for you
	function saveConfig( $origConfig, $module, &$requestData ) {
        $origConfig['enabled'] = false;
		$prefixStr = $origConfig['prefix'].'_';
		$prefixLen = strlen($prefixStr);
		foreach ( $requestData as $key => $value ) {
			if ( strncmp($prefixStr,$key,$prefixLen) === 0 ) {
				$newKey = substr($key,$prefixLen);
                if ( $newKey == 'enabled' ) {
                    if ( $value == 'true' ) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                }
				if ( $newKey == 'mul' ) {
					$value = (float)$value;
				}
				$origConfig[$newKey] = $value;
			}
		}

		if ( isset($origConfig['rows']) && is_array($origConfig['rows']) ) {
			foreach ( $origConfig['rows'] as $i => $row ) {
				if ( !isset($row['enabled']) ) {
					$origConfig['rows'][$i]['enabled'] = false;
				}
				if ( !isset($row['score']) ) {
					$origConfig['rows'][$i]['score'] = 0;
				}
				if ( !isset($row['mul']) ) {
					$origConfig['rows'][$i]['mul'] = 0.0;
				}
			}

			// Handle deletes after we store the row, just so we don't try and delete it and then bring back a zombie row because we still have data in the score field.
			if ( !empty($_REQUEST['deleteRowPrefix']) && $_REQUEST['deleteRowPrefix'] == $origConfig['prefix'] 
				 && !empty($_REQUEST['deleteRow']) && isset($origConfig['rows'][$_REQUEST['deleteRow']]) ) {
				unset($origConfig['rows'][$_REQUEST['deleteRow']]);
			}
		}
		return($origConfig);
	}

	function addConfig( $prefix, $module, &$requestData ) {
		$config = array();
		$config['prefix'] = $prefix;
		$config['module'] = $module;
		$config['weight'] = 1;
		$config['enabled'] = false;
		$config['ruleClass'] = get_class($this);

		$moduleClass = loadBean($module);
		$config['field'] = $requestData['add'][$module];
		if ( isset($moduleClass->field_defs[$config['field']]) ) {
			if ( isset($moduleClass->field_defs[$config['field']]['vname']) ) {
				$config['fieldLabel'] = rtrim($moduleClass->field_defs[$config['field']]['vname'],': ');
			} else {
				$config['fieldLabel'] = $config['field'];
			}
		} else {
			$config['fieldLabel'] = $config['field'];
		}
		
		return($config);
	}

	// Shouldn't need to override
	function addGenericSmarty( $sugar_smarty, $module ) {
		global $mod_strings, $app_strings, $app_list_strings;
		
		$target_strings = return_module_language($GLOBALS['current_language'],$module);
		$sugar_smarty->assign('mod', $mod_strings);
		$sugar_smarty->assign('app', $app_strings);
		$sugar_smarty->assign('list', $app_list_strings);
		$sugar_smarty->assign('target', $target_strings);
		$sugar_smarty->assign('module', $module);
		$sugar_smarty->assign('ruleClass', $this);
		$sugar_smarty->assign('image_path', $GLOBALS['image_path']);
	}

	// We may be passed in rows as arrays or as beans
	function getVal( &$row, $field ) {
		if ( is_array($row) ) {
			if ( isset($row[$field]) ) {
				return($row[$field]);
			}
		} else {
			if ( isset($row->$field) ) {
				return($row->$field);
			}
		}
		return('');
	}
	function createScoreEntry($config, $value, $score, $mul ) {
		if ( !isset($config['weight']) ) { $config['weight'] = 0; }
		$scoreEntry = array();
		$scoreEntry['rule_id'] = $config['prefix'];
		$scoreEntry['rule_data'] = $value;
		$scoreEntry['score_add'] = $score * $config['weight'];
		$scoreEntry['score_mul'] = $mul;

		return($scoreEntry);
	}

	// These two functions handle preparing and displaying the HTML to handle each individual configuration
	function prepare( $module, $config ) {
		$sugar_smarty = new Sugar_Smarty();
		$sugar_smarty->assign('config', $config);
		$sugar_smarty->assign('prefix', $config['prefix']);
		$this->addGenericSmarty($sugar_smarty, $module);
		$config['smarty'] = $sugar_smarty;
		if ( !$this->isSpecialField ) {
			$config['label'] = translate($config['fieldLabel'],$module);
		} else {
			$config['label'] = translate('LBL_'.$this->ruleName.'_LABEL','Score');
		}
		return($config);
	}

	function render( $module, $config ) {
		$config = $this->prepare($module,$config);

		$filename = 'modules/Score/RuleClasses/'.get_class($this).'.tpl';
		if ( file_exists('custom/'.$filename) ) {
			$filename = 'custom/'.$filename;
		}

		$config['html'] = $config['smarty']->fetch($filename);
		return($config);
	}

	// These two functions give your rule an option to ask an additional question when you are adding a new rule
	function prepareAdd( $module ) {
		$sugar_smarty = new Sugar_Smarty();
		$this->addGenericSmarty($sugar_smarty, $module);
		return($sugar_smarty);
	}

	function renderAdd( $module ) {
		$sugar_smarty = $this->prepareAdd($module);

		$filename = 'modules/Score/RuleClasses/'.get_class($this).'Add.tpl';
		if ( file_exists('custom/'.$filename) ) {
			$filename = 'custom/'.$filename;
		}

		if ( ! file_exists($filename) ) {
			return('');
		} else {
			return($sugar_smarty->fetch($filename));
		}
	}

	function scoreRow( &$config, &$module, &$row, &$scoreList ) {
		// This needs to be implemented for your specific rule
	}

	function getScoreInfo ( &$scoreRow, &$config, &$module ) {
		if ( !$this->isSpecialField ) {
			$info['name'] = translate($config['fieldLabel'],$module);
		} else {
			$info['name'] = translate('LBL_'.$this->ruleName.'_LABEL','Score');
		}
		$info['val'] = $scoreRow['rule_data'];		
		return($info);
	}
}
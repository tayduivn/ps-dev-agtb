<?PHP
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 */
class Score extends Basic {
	var $new_schema = true;
	var $module_dir = 'Score';
	var $object_name = 'Score';
	var $table_name = 'score';
	var $importable = false;
	
	var $id;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $modified_by_name;
	var $modified_user_link;
	var $created_by;
	var $created_by_name;
	var $created_by_link;
	var $deleted;

	// Fields specific to this module
	var $rule_name;
	var $rule_data;
	var $target_id;
	var $target_module;
	var $source_id;
	var $source_module;
	var $score_add;
	var $score_mul;
	var $dirty_flag;
		
	function Score () {
		parent::Basic();
		
		// No row-level security, if they can see the parent of the score, they should be able to see the score
		$this->disable_row_level_security = true;
	}

	static function getScoreModuleData () {
		static $scoreModules;

		if ( !isset($scoreModules) ) {
			$scoreModules = array(
				'Touchpoints' => array('scoreField'=>'score','parentModule'=>'','parentModuleField'=>''),
				'Interactions' => array('scoreField'=>'score','parentModule'=>'LeadContacts','parentModuleField'=>'parent_id'),
				'LeadContacts' => array('scoreField'=>'score','parentModule'=>'LeadAccounts','parentModuleField'=>'leadaccount_id'),
				'LeadAccounts' => array('scoreField'=>'score','parentModule'=>'','parentModuleField'=>''),
				);
			
			// And a hook for adding additional scored modules
			if ( file_exists('custom/modules/Score/scoreModules.php') ) {
				require_once('custom/modules/Score/scoreModules.php');
			}
		}
		return($scoreModules);
	}

	static function getRuleClasses () {
		// This is only used in the admin screen, so we shouldn't cache this

		// First: Get a list of all classes and their filenames
		$classDirs = array('modules/Score/RuleClasses/','custom/modules/Score/RuleClasses/');
		$fileList = array();
		foreach ( $classDirs as $path ) {
			if ( !is_dir($path) ) {
				continue;
			}
			$dir = dir($path);
			while ( ($file = $dir->read()) != FALSE ) {
				if ( $file{0} == '.' ) { continue; }
				if ( substr($file,-4) == '.php' ) {
					$className = substr($file,0,-4);
					if ( strtoupper(substr($className,-4)) == 'CSTM' ) {
						// Remove the standard rule from the list if we have a custom version
						unset($fileList[substr($className,0,-4)]);
					}
					$fileList[$className] = $path.$file;
				}
			}
			$dir->close();
		}

		// Secondly, check each class for the list of modules they support
		$classList = array();
		foreach ( $fileList as $className => $filePath ) {
			require_once($filePath);
			$tmpClass = new $className();
			if ( ! is_array($tmpClass->allowedModules) ) {
				// This might happen if someone is trying to disable a rule
				$tmpClass->allowedModules = array();
			}
			foreach ( $tmpClass->allowedModules as $module ) {
				if ( !isset($classList[$module]) || ! is_array($classList[$module]) ) {
					$classList[$module] = array();
				}
				$classList[$module][$className] = $filePath;
			}
		}

		// Thirdly, store this in the cache
		if ( ! is_dir('cache/modules/Score/') ) {
			mkdir_recursive('cache/modules/Score/');
		}
		$fd = fopen('cache/modules/Score/RuleClassList.tmp.php','w');
		fwrite($fd,'<'.'?php'."\n// GENERATED AUTOMATICALLY\n\$classList = ".var_export($classList,true).';');
		fclose($fd);
		// Do a rename so the file is changed atomically
		rename('cache/modules/Score/RuleClassList.tmp.php','cache/modules/Score/RuleClassList.php');
		
		return($classList);
	}

	static function getRuleClassesForModule ( $module ) {
        static $classCache;

        if ( isset($classCache[$module]) ) {
            return($classCache[$module]);
        }

		if ( ! file_exists('cache/modules/Score/RuleClassList.php') ) {
			$classList = self::getRuleClasses();
		} else {
			require('cache/modules/Score/RuleClassList.php');
		}

		
		if ( !isset($classList[$module]) || count($classList[$module]) == 0 ) {
			$classList[$module] = array();
		}
		if ( !isset($classList['*']) || count($classList['*']) == 0 ) {
			$classList['*'] = array();
		}
		// Merge the module specific rules in with the wildcard rules
		$moduleClassList = array_merge($classList[$module],$classList['*']);
		
		$moduleClasses = array();
		foreach( $moduleClassList as $className => $filePath ) {
			require_once($filePath);
			$moduleClasses[$className] = new $className();
		}

        $classCache[$module] = $moduleClasses;

		return($classCache[$module]);
	}

	static function getModuleConfigs( $module ) {
		if ( ! is_dir('custom/modules/Score/ScoreConfigs/') ) {
			mkdir_recursive('custom/modules/Score/ScoreConfigs/');
		}
		if ( ! file_exists('custom/modules/Score/ScoreConfigs/'.$module.'.php') ) {
			$config = array('enabled'=>false,'apply_mult'=>'curr','rules'=>array());
		} else {
			require('custom/modules/Score/ScoreConfigs/'.$module.'.php');
			$config = $scoreConfig[$module];
		}
		return($config);
	}

	static function saveModuleConfigs( $module, $configs ) {
		if ( empty($configs) ) {
			$configs = array();
		}
		if ( ! is_dir('custom/modules/Score/ScoreConfigs/') ) {
			mkdir_recursive('custom/modules/Score/ScoreConfigs/');
		}
		$fd = fopen('custom/modules/Score/ScoreConfigs/'.$module.'.tmp.php','w');
		fwrite($fd,'<'.'?php'."\n// GENERATED AUTOMATICALLY\n\$scoreConfig['".$module."'] = ".var_export($configs,true).';');
		fclose($fd);
		// Do a rename so the file is changed atomically
		rename('custom/modules/Score/ScoreConfigs/'.$module.'.tmp.php','custom/modules/Score/ScoreConfigs/'.$module.'.php');
	}

	static function scoreBean ( $bean, $updateParent = true ) {
		// BEGIN SADEK: PARDOT SUGARINTERNAL CUSTOMIZATION - IT REQUEST 12823 - DISABLE SCORING FOR ALL MODULES
		return;
		// END SADEK: PARDOT SUGARINTERNAL CUSTOMIZATION - IT REQUEST 12823 - DISABLE SCORING FOR ALL MODULES
		
		if ( ! self::enabledForModule($bean->module_dir) ) {
			return;
		}

		$moduleConfig = self::getModuleConfigs($bean->module_dir);
		$scoreConfigs = self::getScoreModuleData();
		$myConfig = $scoreConfigs[$bean->module_dir];

		if ( empty($bean->id) ) {
			// Need to set an ID before we can score properly
			$bean->id = create_guid();
			$bean->new_with_id = true;
		}
		
		if ( empty($myConfig['parentModuleField']) || empty($myConfig['parentModule']) )  {
			// Can't update a parent if we don't know the parent's information
			$updateParent = false;
		}
		$parent_id = '';
		if ( !empty($myConfig['parentModuleField']) ) {
			$parentFieldName = $myConfig['parentModuleField'];
			$parent_id = $bean->$parentFieldName;
		}
		if ( empty($parent_id) ) {
			$updateParent = false;
		}
		$parent_module = $myConfig['parentModule'];
		$scoreFieldName = $myConfig['scoreField'];
		$oldScore = $bean->$scoreFieldName;
        $newScore = self::scoreRow($bean->module_dir, $bean->id, $bean, $moduleConfig, self::getRuleClassesForModule($bean->module_dir), $parent_module, $parent_id);
        $bean->$scoreFieldName = $newScore;
		//$GLOBALS['log']->fatal('"'.$scoreFieldName.'" -- OLD SCORE: '.$oldScore.', NEW SCORE: '.$newScore.'/'.$bean->$scoreFieldName);
		$scoreDiff = abs($oldScore - $newScore);
		if ( ($scoreDiff > 0.01) && $updateParent ) {
			$GLOBALS['log']->debug("Updating parent module's score: $parent_module, id: $parent_id, curr_module: ".$bean->module_dir);
			$parentClass = loadBean($parent_module);
			$parentClass->disable_row_level_security = true;
			$parentClass->update_date_modified = false;
			$parentClass->update_modified_by = false;
			$parentClass->retrieve($parent_id);
			// Will automatically update score when it saves
			$parentClass->save();
		}

	}
    
    static function getScoreEntries ( &$module, &$id, &$record, &$config, &$ruleClasses ) {
        // This function is used by both the single record scoring system and the score board
        global $db;

		if ( ! self::enabledForModule($module) ) {
			return array();
		}

		$scoreList = array();
		foreach ( $config['rules'] as &$rule ) {
			if ( $rule['enabled'] == 'true' ) {
				$ruleClasses[$rule['ruleClass']]->scoreRow($rule,$module,$record,$scoreList);
			}
		}

        // Fetch all child record scores that reference this record
        $ret = $db->query("SELECT * FROM score WHERE target_id = '".$db->quote($id)."' AND target_module = '".$db->quote($module)."' AND ( source_id <> '".$db->quote($id)."' AND source_module <> '".$db->quote($module)."') ",true);
        
        while ( $scoreRow = $db->fetchByAssoc($ret) ) {
            $scoreRow['childScore'] = true;
            $scoreList[] = $scoreRow;
        }

        //$GLOBALS['log']->fatal(print_r($scoreList,true));

        return($scoreList);
    }

	static function scoreRow ( $module, $id, &$record, &$config, $ruleClasses, $parent_module = '', $parent_id = '') {
		global $db;
		if ( ! self::enabledForModule($module) ) {
			return;
		}

        $scoreList = self::getScoreEntries($module, $id, $record, $config, $ruleClasses);

        $scoreAdd = 0;
        $scoreMul = 0;
        $scoreAddCurrRecord = 0;
        $scoreMulCurrRecord = 0;
        foreach ( $scoreList as $scoreRow ) {
            $scoreAdd += $scoreRow['score_add'];
            $scoreMul += $scoreRow['score_mul'];
            if ( !isset($scoreRow['childScore']) || $scoreRow['childScore'] != true ) {
                $scoreAddCurrRecord += $scoreRow['score_add'];
                $scoreMulCurrRecord += $scoreRow['score_mul'];
            }
        }
        //$GLOBALS['log']->fatal("Score Add: $scoreAdd / $scoreAddCurrRecord");
        //$GLOBALS['log']->fatal("Score Mul: $scoreMul / $scoreMulCurrRecord");

        $scoreRow = array();
        $ret = $db->query("SELECT id FROM score WHERE source_id = '".$db->quote($id)."' AND source_module = '".$db->quote($module)."'",true);
        $scoreRow = $db->fetchByAssoc($ret);
        if ( isset($scoreRow['id']) ) {
            $scoreRow['id'] = $scoreRow['id'];
        }

        $scoreRow['modified_user_id'] = $GLOBALS['current_user']->id;
        $scoreRow['created_by'] = $scoreRow['modified_user_id'];
        $scoreRow['date_entered'] = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $scoreRow['date_modified'] = $scoreRow['date_entered'];
        $scoreRow['deleted'] = '0';
        $scoreRow['is_dirty'] = '0';
		$scoreRow['source_id'] = $id;
		$scoreRow['source_module'] = $module;
        $scoreRow['score_add'] = $scoreAddCurrRecord;
        $scoreRow['score_mul'] = $scoreMulCurrRecord;
        $scoreRow['rule_id'] = 'SING';

        if ( !empty($parent_module) ) {
            $scoreRow['target_id'] = $parent_id;
            $scoreRow['target_module'] = $parent_module;            
        } else {
            $scoreRow['target_id'] = $id;
            $scoreRow['target_module'] = $module;
        }

		if ( $config['apply_mult'] = 'record' ) {
			$score = $scoreAdd * (($scoreMul/100)+1);
            $scoreRow['score_add'] = $score;
            $scoreRow['score_mul'] = 0;
		} else {
			$score = $scoreAdd;
            $scoreRow['score_add'] = $score;
            $scoreRow['score_mul'] = $scoreMul;
		}
        self::insertScoreRow($module,$id,$scoreRow);

        //$GLOBALS['log']->fatal("Returned score: $score");
		return($score);
	}
	static function insertScoreRow( &$module, &$id, &$scoreRow ) {
		global $db;

        if ( empty($scoreRow['id']) ) {
            $scoreRow['id'] = create_guid();
            $scoreCols = array();
            foreach ( $scoreRow as $col ) {
                $scoreCols[] = $db->quote($col);
            }
            $query = "INSERT INTO score (".implode(',',array_keys($scoreRow)).") VALUES ('".implode("','",$scoreCols)."')";
        } else {
            // It's an update
            $query = "UPDATE score SET ";
            foreach ( $scoreRow as $colName => $value ) {
                $query .= $colName." = '".$db->quote($value)."', ";
            }
            $query = rtrim($query,', ')." WHERE id = '".$db->quote($scoreRow['id'])."'";
        }
        $db->query($query,true);
	}
	
	static function markDirty( $type, $module, $id = '' ) {
        // No longer used with direct SQL rescoring
        /*
		global $db;
		
		switch ( $type ) {
			case 'target':
				$module_col = 'target_module';
				$id_col = 'target_id';
				break;
			case 'source':
				$module_col = 'source_module';
				$id_col = 'source_id';
				break;
			case 'taget_rule':
				$module_col = 'target_module';
				$id_col = 'rule_id';
				break;
			case 'source_rule':
				$module_col = 'source_module';
				$id_col = 'rule_id';
				break;
		}
		if ( $id == '' ) {
			$db->query("UPDATE score SET is_dirty = 1 WHERE ".$module_col." = '".$db->quote($module)."'",true);
		} else {
			$db->query("UPDATE score SET is_dirty = 1 WHERE ".$module_col." = '".$db->quote($module)."' AND ".$id_col." = '".$db->quote($id)."'",true);			
		}
        */
	}

    static function sqlRescoreChunk($module,$bean,$id='',$offset=0,$limit=0) {
        global $db;
        
        $scoreModules = self::getScoreModuleData();
        $moduleData = $scoreModules[$module];
        
        $ruleClasses = self::getRuleClassesForModule($module);
        $moduleRules = self::getModuleConfigs($module);
        
        $beanHasCustomFields = false;
        foreach ( $bean->field_defs as $def ) {
            if ( substr($def['name'],-2) == '_c' ) {
            $beanHasCustomFields = true;
            break;
            }
        }
        $haveChild = false;
        foreach ( $scoreModules as $tmpModule => $tmpModuleData ) {
            if ( $tmpModuleData['parentModule'] == $module ) {
                $haveChild = $tmpModule;
                break;
            }
        }

        
        // get each of the rule parts
        $addParts = array();
        $mulParts = array();
        foreach ( $moduleRules['rules'] as $prefix => $rule ) {
            //$GLOBALS['log']->fatal(print_r($rule,true));
//        echo('<pre>'.print_r($rule,true).'</pre>');
            if ( $rule['enabled'] == true ) {
                $ruleSQL = $ruleClasses[$rule['ruleClass']]->getScoreRowSQL($rule,$module);
                $addParts[] = $ruleSQL['scoreSQL'];
                $mulParts[] = $ruleSQL['mulSQL'];
            }
        }

        if ( $haveChild ) {
            // Add in the child scores to the current record's score
            $addParts[] = "( SELECT SUM(score_add) FROM score WHERE score.target_id = target.id AND score.target_module = '".$db->quote($module)."' )";
            $mulParts[] = "( SELECT SUM(score_mul) FROM score WHERE score.target_id = target.id AND score.target_module = '".$db->quote($module)."' )";
        }

        if ( count($addParts) < 1 ) {
            $addParts[] = " 0 ";
        }
        if ( count($mulParts) < 1 ) {
            $mulParts[] = " 0 ";
        }
        
        
        $giant_query = "INSERT INTO score (id,name,date_entered,date_modified,modified_user_id,created_by,rule_data,target_id,target_module,source_id,source_module,score_add,score_mul,is_dirty,rule_id) select UUID() AS id, '' AS name, now() AS date_entered, now() AS date_modified, '1' AS modified_user_id, '1' AS created_by, now() AS rule_data, ";
        if ( empty($moduleData['parentModule']) ) {
            $giant_query .= " target.id AS target_id, '".$db->quote($module)."' AS target_module, ";
        } else {
            $giant_query .= " target.".$moduleData['parentModuleField']." AS target_id, '".$db->quote($moduleData['parentModule'])."' AS target_module, ";
        }
        $giant_query .= " target.id AS source_id, '".$db->quote($module)."' AS source_module, \n";
        $giant_query .= implode($addParts,"\n + ")."\n AS score_add, \n";
        $giant_query .= implode($mulParts,"\n + ")."\n AS score_mul, \n";
        $giant_query .= " 0 AS is_dirty, 'MASS' AS rule_id FROM ".$bean->table_name." AS target ";
        if ( $beanHasCustomFields ) {
            $giant_query .= "INNER JOIN ".$bean->table_name."_cstm AS target_c ON target.id = target_c.id_c ";
        }
        $giant_query .= "\n WHERE target.deleted = 0 ";
        if ( !empty($id) ) {
            $giant_query .= " AND target.id = '".$db->quote($id)."' ";
        }
        $giant_query .= "\n ORDER BY target.id";
        if ( $limit != 0 ) {
            $db->limitQuery($giant_query,$offset,$limit,true);            
        } else {
            $db->query($giant_query,true);
        }
        //$GLOBALS['log']->fatal($giant_query);
    }

    static function sqlCopyScores($module,$bean,$id='') {
        global $db;

        $scoreModules = self::getScoreModuleData();
        $moduleData = $scoreModules[$module];
        $moduleRules = self::getModuleConfigs($module);

        $finalQuery = "UPDATE ".$bean->table_name." AS target INNER JOIN score AS s ON ( target.id = s.source_id AND s.source_module = '".$db->quote($module)."' ) SET ".$moduleData['scoreField']." = s.score_add ";

        if ( $moduleRules['apply_mult'] == 'record' ) {
            // Pull in the multiplier for myself.
            $finalQuery .= " * ( 1 + ( s.score_mul / 100 ) ) ";
        }
        if ( !empty($id) ) {
            $finalQuery .= " WHERE target.id = '".$db->quote($id)."' ";
        }
        $db->query($finalQuery,true);
        //$GLOBALS['log']->fatal($finalQuery);
    }

    static function enabledForModule($module) {
        $configs = Score::getModuleConfigs($module);
        if ( isset($configs) && isset($configs['enabled']) && $configs['enabled'] == true ) {
            return true;
        } else {
            return false;
        }
    }

    static function sqlRescoreModule($module,$id='') {
        global $db;
        
        if ( ! self::enabledForModule($module) ) {
            // Don't try to rescore a disabled module
            return;
        }

        $bean = loadBean($module);
        $db->query("DELETE FROM score WHERE source_module = '".$db->quote($module)."'",true);
        self::sqlRescoreChunk($module,$bean,$id);
        self::sqlCopyScores($module,$bean,$id);
    }
    
}

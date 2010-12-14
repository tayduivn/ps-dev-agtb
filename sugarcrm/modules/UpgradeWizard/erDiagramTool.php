<?php
//FILE SUGARCRM flav=int ONLY
///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
$response = '';
function createDatabaseForER_Diagram($sql) {
    global $mod_strings;
    global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
    global $sugar_config;

	$setup_db_database_name='er_diagram';
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];

    $response= "<h1><b>{$mod_strings['LBL_CREATE_DB_ER_DIAGRAM']} <i>{$setup_db_database_name}</i> {$mod_strings['LBL_CREATE_DB_ER_DIAGRAM_2']} <i>{$setup_db_host_name}</i>...</b></h1>";

	$_SESSION['setup_db_type']='mysql';
    switch($_SESSION['setup_db_type']) {
        case 'mysql':

             if(isset($_SESSION['mysql_type'])){
                $link = @mysqli_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                $drop = 'DROP DATABASE IF EXISTS '.$setup_db_database_name;
                @mysqli_query($link, $drop);

                $query = 'CREATE DATABASE `' . $setup_db_database_name . '` CHARACTER SET utf8 COLLATE utf8_general_ci';
                @mysqli_query($link, $query);
                mysqli_close($link);

            }else{
                $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
                $drop = 'DROP DATABASE IF EXISTS '.$setup_db_database_name;
                @mysql_query($drop, $link);

                $query = 'CREATE DATABASE `' . $setup_db_database_name . '` CHARACTER SET utf8 COLLATE utf8_general_ci';
                @mysql_query($query,$link);
				mysql_select_db('er_diagram');
				$queArray = array();
				$queArray = explode(';',$sql);
				$count = 0;
                for($i=0;$i<count($queArray);$i++ ){
					$query = $queArray[$i];
					//echo '</br>';
					//$q = "CREATE TABLE relationships (id char(36) NOT NULL ,relationship_name varchar(150) NOT NULL ,lhs_module varchar(100) NOT NULL ,lhs_table varchar(64) NOT NULL ,lhs_key varchar(64) NOT NULL ,rhs_module varchar(100) NOT NULL ,rhs_table varchar(64) NOT NULL ,rhs_key varchar(64) NOT NULL ,join_table varchar(64) NULL ,join_key_lhs varchar(64) NULL ,join_key_rhs varchar(64) NULL ,relationship_type varchar(64) NULL ,relationship_role_column varchar(64) NULL ,relationship_role_column_value varchar(50) NULL ,reverse bool DEFAULT '0' NULL ,deleted bool DEFAULT '0' NULL , PRIMARY KEY (id), KEY idx_rel_name (relationship_name)) CHARACTER SET utf8 COLLATE utf8_general_ci";
					mysql_query($query,$link);
					$query = '';
				}
                mysql_close($link);

            }

        break;

        case 'mssql':
        $connect_host = "";
        $setup_db_host_instance = trim($setup_db_host_instance);
        if (empty($setup_db_host_instance)){
            $connect_host = $setup_db_host_name ;
        }else{
            $connect_host = $setup_db_host_name . "\\" . $setup_db_host_instance;
        }
        if(isset($_SESSION['mssql_type'])){
            $link = @sqlsrv_connect($connect_host, array( 'UID' => $setup_db_admin_user_name, 'PWD' => $setup_db_admin_password));
            $setup_db_database_name = str_replace(' ', '_', $setup_db_database_name);  // remove space

            //create check to see if this is existing db
            $check = "SELECT count(name) num FROM master..sysdatabases WHERE name = N'".$setup_db_database_name."'";
            $tableCntRes = sqlsrv_query($link,$check);
            $tableCnt= sqlsrv_fetch_array($tableCntRes);

            //if this db already exists, then drop it
            if($tableCnt[0]>0){
                $drop = "DROP DATABASE $setup_db_database_name";
               @ sqlsrv_query($link,$drop);
            }

            //create db
            $query = 'create database '.$setup_db_database_name;
            @sqlsrv_query($link,$query);
            @sqlsrv_close($link);
        }
        else {
            $link = @mssql_connect($connect_host, $setup_db_admin_user_name, $setup_db_admin_password);
            $setup_db_database_name = str_replace(' ', '_', $setup_db_database_name);  // remove space

            //create check to see if this is existing db
            $check = "SELECT count(name) num FROM master..sysdatabases WHERE name = N'".$setup_db_database_name."'";
            $tableCntRes = mssql_query($check);
            $tableCnt= mssql_fetch_row($tableCntRes);

            //if this db already exists, then drop it
            if($tableCnt[0]>0){
                $drop = "DROP DATABASE $setup_db_database_name";
               @ mssql_query($drop);
            }

            //create db
            $query = 'create database '.$setup_db_database_name;
            @mssql_query($query);
            @mssql_close($link);
        }
        break;
    }
    $setup_db_database_name=$sugar_config['dbconfig']['db_name'];
    echo $mod_strings['LBL_PERFORM_DONE'];
    return $response;
}

function createSchema($execute=false,$return=false,&$response){

	global $current_user, $beanFiles;
	global $dictionary;
	set_time_limit(3600);
	$commentsAll = array();
	$commentsNot = array();

	$db = &DBManagerFactory::getInstance();
	foreach( $beanFiles as $bean => $file ){
    	require_once( $file );
	}
	VardefManager::clearVardef();
	$FK_ARRAY = array();
	$FK_CREATE_ARRAY = array();
	$dictionary = array() ;
    require ('modules/TableDictionary.php') ;
	$dictionary = $GLOBALS['dictionary'];
	// add non-module Beans to this array to keep the installer from erroring.
	$nonStandardModules = array (
	    //'Tracker',
	);

    $que = "select * from relationships";
    $query = $db->query($que);
    $sql_fk = '';
   	while($rel_def = $db->fetchByAssoc($query))
	    {
          if($rel_def['relationship_type']== 'one-to-many'){
              if($rel_def['rhs_table'] != null){
            		//$new_FK ='ALTER TABLE '.$tbl_name.' ADD FOREIGN KEY '.$fk_name.'('.$column_name).' REFERENCES '. $reference_tbl_name ($ref_columns);
            		//$new_FK ='FOREIGN KEY FK_'.$rel_def['relationship_name'].' '.$rel_def['rhs_table'].'('.$rel_def['rhs_key'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';
					$just_fk ='ALTER TABLE '.$rel_def['rhs_table'].' ADD FOREIGN KEY FK_'.$rel_def['rhs_table'].'_'.$rel_def['rhs_key'].'('.$rel_def['rhs_key'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';

                    if(!strstr($sql_fk,$just_fk)){
                    	$sql_fk .= $just_fk.";"."\n";
                    }

					$new_FK ='FOREIGN KEY FK_'.$rel_def['rhs_table'].'_'.$rel_def['rhs_key'].'('.$rel_def['rhs_key'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';

            		if(isset($FK_ARRAY[$rel_def['rhs_table']]) && !empty($FK_ARRAY[$rel_def['rhs_table']])){
	            		if(!strstr($FK_ARRAY[$rel_def['rhs_table']],$new_FK)){
	            				$FK_ARRAY[$rel_def['rhs_table']] = $FK_ARRAY[$rel_def['rhs_table']].','.$new_FK;
	            		}
            		}
            		else{
            			$FK_ARRAY[$rel_def['rhs_table']] = $new_FK;
            		}
                }
                	//echo 'lhs_table '.$rel_def['lhs_table'].'</br>';
                	//echo 'rhs_table '.$rel_def['rhs_table'].'</br>';
                	//'ALTER TABLE '.$tbl_name.' ADD FOREIGN KEY '.$fk_name.'('.$column_name).' REFERENCES '. $reference_tbl_name ($ref_columns);
            }
            if($rel_def['relationship_type']== 'many-to-many'){
            	//echo 'COMES TO MANY *********************** '.$rel_def['relationship_name'].'</br>';
            	//$new_FK_left ='FOREIGN KEY FK_'.$rel_def['relationship_name'].'_left'.' '.$rel_def['join_table'].'('.$rel_def['join_key_lhs'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';
            	//$new_FK_right ='FOREIGN KEY FK_'.$rel_def['relationship_name'].'_right'.' '.$rel_def['join_table'].'('.$rel_def['join_key_rhs'].')'.' REFERENCES '. $rel_def['rhs_table'].'('.$rel_def['rhs_key'].')';
            	$just_FK_left ='ALTER TABLE '.$rel_def['join_table'].' ADD FOREIGN KEY FK_'.$rel_def['join_table'].'_'.$rel_def['join_key_lhs'].'('.$rel_def['join_key_lhs'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';
        	    if(!strstr($sql_fk,$just_FK_left)){
                	$sql_fk .= $just_FK_left.";"."\n";
                 }

            	//$sql_fk .= $just_FK_left.";"."\n";
            	$just_FK_right ='ALTER TABLE '.$rel_def['join_table'].' ADD FOREIGN KEY FK_'.$rel_def['join_table'].'_'.$rel_def['join_key_rhs'].'('.$rel_def['join_key_rhs'].')'.' REFERENCES '. $rel_def['rhs_table'].'('.$rel_def['rhs_key'].')';
                if(!strstr($sql_fk,$just_FK_right)){
                	$sql_fk .= $just_FK_right.";"."\n";
                 }
                //$sql_fk .= $just_FK_right.";"."\n";

            	$new_FK_left ='FOREIGN KEY FK_'.$rel_def['join_table'].'_'.$rel_def['join_key_lhs'].'('.$rel_def['join_key_lhs'].')'.' REFERENCES '. $rel_def['lhs_table'].'('.$rel_def['lhs_key'].')';
            	$new_FK_right ='FOREIGN KEY FK_'.$rel_def['join_table'].'_'.$rel_def['join_key_rhs'].'('.$rel_def['join_key_rhs'].')'.' REFERENCES '. $rel_def['rhs_table'].'('.$rel_def['rhs_key'].')';
        		if(isset($FK_ARRAY[$rel_def['join_table']]) && !empty($FK_ARRAY[$rel_def['join_table']])){
            		if(!strstr($FK_ARRAY[$rel_def['join_table']],$new_FK_left)){
            			$FK_ARRAY[$rel_def['join_table']] = $FK_ARRAY[$rel_def['join_table']].','.$new_FK_left;
            		}
            		if(!strstr($FK_ARRAY[$rel_def['join_table']],$new_FK_right)){
            			$FK_ARRAY[$rel_def['join_table']] = $FK_ARRAY[$rel_def['join_table']].','.$new_FK_right;
            		}
        		}
        		else{
        			$FK_ARRAY[$rel_def['join_table']] = $new_FK_left.','.$new_FK_right;
        		}
            }
	    }
	$processed_tables = array();

	if($processed_tables != null){
		unset($processed_tables);
	}

	$sql='';
    $sqlArray = array();
	foreach( $beanFiles as $bean => $file ) {
		$doNotInit = array('Scheduler', 'SchedulersJob');
		if(in_array($bean, $doNotInit)) {
			$focus = new $bean(false);
		} else {
		    $focus = new $bean();
		}
		if(!isset($focus->table_name)){
			continue;
		}
	    $tablename = $focus->table_name;
		$tablename = $focus->getTableName();
		$fielddefs = $focus->getFieldDefinitions();
		$indices = $focus->getIndices();
        $currCommetns =array();
        $commentsAll[$tablename] = array();
		if(file_exists("modules/".$focus->module_dir."/vardefs.php")){
		    include("modules/".$focus->module_dir."/vardefs.php");
		    if(empty($dictionary) || !empty($GLOBALS['dictionary'][$focus->object_name]))
			{
				$dictionary = $GLOBALS['dictionary'];
			}
            if ( isset($dictionary[$focus->object_name]['comment']) ) {
                $currComments['table'] =$dictionary[$focus->object_name]['comment'];
            } else {
                $currComments['table'] = '';
            }
		    $currComments['columns'] = array();

		    //$commentsAll[$tablename] = array();
		    //$commentsAll[$dictionary[$focus->object_name]]= $dictionary[$focus->object_name]['comment'];
	        //$commentsAll[$dictionary[$focus->object_name]]['columns'] =array();
	        //print_r($dictionary[$focus->object_name]);
	        $colsArray = array();
	        if($dictionary[$focus->object_name]['fields'] != null){
		        foreach($dictionary[$focus->object_name]['fields'] as $key=>$vals){
		    		if(isset($vals['comment']) && $vals['comment'] != null){
		    			$colsArray[$key]=$vals['comment'];
		    		}
		        }
	        }

	        $currComments['columns'] = $colsArray;
	        //print_r($currComments);
	        if($colsArray != null && sizeof($colsArray)>0){
	        	$commentsAll[$tablename]=$currComments;
	        }

	    }
	   if($tablename != null && !empty($tablename) && !in_array($tablename, $processed_tables)) {
	        /*
	        if(!file_exists("modules/".$focus->module_dir."/vardefs.php")){
	            continue;
	        }
	        if(!in_array($bean, $nonStandardModules)) {
	            require_once("modules/".$focus->module_dir."/vardefs.php"); // load up $dictionary
	            if($dictionary[$focus->object_name]['table'] == 'does_not_exist') {
	                continue; // support new vardef definitions
	            }
	        } else {
	        	continue; //no further processing needed for ignored beans.
	        }
            */
            if($dictionary[$focus->object_name]['table'] == 'does_not_exist') {
	                continue; // support new vardef definitions
	         }
			$curr_sql = $db->getHelper()
		                ->createTableSQLParams($tablename, $fielddefs, $indices);
            $curr_sql_f = '';

		    if(isset($FK_ARRAY[$tablename]) && $FK_ARRAY[$tablename] != null){
                //print_r(",".$FK_ARRAY[$tablename].") CHARACTER SET").'</br>';
		    	$curr_sql_f=str_replace(") CHARACTER SET utf8 COLLATE utf8_general_ci",",".$FK_ARRAY[$tablename].") ENGINE=INNODB",$curr_sql);
		    }

		    if($curr_sql_f != null) $curr_sql = $curr_sql_f;
             //print_r($curr_sql).'</br>';
		    $sqlArray[$tablename] = $curr_sql;

	        // table has not been setup...we will do it now and remember that
	        $processed_tables[] = $tablename;
	    }
	}

		include ('modules/TableDictionary.php');
		foreach ($dictionary as $meta) {
			if($meta =='relationships') continue;

			$tablename = $meta['table'];
			$fielddefs = $meta['fields'];
            if ( !empty($meta['indices']) ) {
                $indices = $meta['indices'];
            } else {
                $indices = array();
            }

			//if(!isset($tablename) ||  $tablename == null || empty($tablename))
				//continue;
			if($tablename == 'does_not_exist') {
	                continue; // support new vardef definitions
	            }
			if($tablename != null && !empty($tablename) && !in_array($tablename, $processed_tables)) {
				$curr_sql = $db->getHelper()
			                ->createTableSQLParams($tablename, $fielddefs, $indices);
	            $curr_sql_f = '';

			    if(isset($FK_ARRAY[$tablename]) && $FK_ARRAY[$tablename] != null){
	                //print_r(",".$FK_ARRAY[$tablename].") CHARACTER SET").'</br>';
			    	$curr_sql_f=str_replace(") CHARACTER SET utf8 COLLATE utf8_general_ci",",".$FK_ARRAY[$tablename].") ENGINE=INNODB",$curr_sql);
			    }

			    if($curr_sql_f != null) $curr_sql = $curr_sql_f;
	             //print_r($curr_sql).'</br>';
			    $sqlArray[$tablename] = $curr_sql;
				//$sql .= $db->repairTableParams($tablename, $fielddefs, $indices, $execute);
				/*
				if($dictionary['comment']){
				    $commentsAll[$dictionary[$focus->object_name]['table']]= $dictionary[$focus->object_name]['comment'];
				 }

			      $commentsAll[$dictionary[$focus->object_name]['table']]['columns'] =array();
			        //print_r($dictionary[$focus->object_name]);
			        foreach($dictionary[$focus->object_name]['fields'] as $key=>$vals){
			    		if(isset($vals['comment']) && $vals['comment'] != null){
			    			$commentsAll[$dictionary[$focus->object_name]['table']]['columns'][$key]=$vals['comment'];
			    		}
			        }

			    $currComments = '';
	          	if(isset($dictionary[$focus->object_name]['comment'])){
	          		$currComments['table'] =$dictionary[$focus->object_name]['comment'];
	          	}
	          	*/
                $currComments = '';
			    //$commentsAll[$dictionary[$focus->object_name]]= $dictionary[$focus->object_name]['comment'];
		        //$commentsAll[$dictionary[$focus->object_name]]['columns'] =array();
		        //print_r($dictionary[$focus->object_name]);
		        $colsArray = array();
		        /*
		        if($dictionary[$focus->object_name]['fields'] != null){
			        foreach($dictionary[$focus->object_name]['fields'] as $key=>$vals){
			    		if(isset($vals['comment']) && $vals['comment'] != null){
			    			$colsArray[$key]=$vals['comment'];
			    		}
			        }
		        }
		        */
				if(isset($dictionary[$tablename])){
                    if ( !empty($dictionary[$tablename]['comment']) ) {
                        $currComments['table'] =$dictionary[$tablename]['comment'];
                    } else {
                        $currComments['table'] = '';
                    }
			        if($dictionary[$tablename]['fields'] != null){
				        foreach($dictionary[$tablename]['fields'] as $vals){
				    		if(isset($vals['comment']) && $vals['comment'] != null){
				    			$colsArray[$vals['name']]=$vals['comment'];
				    		}
				        }
			        }
		        }
		        //print_r($currComments);
		        if($colsArray != null && sizeof($colsArray)>0){
		        	$currComments['columns'] = array();
		        	$currComments['columns'] = $colsArray;
		        }
		        if(!empty($currComments) && (!empty($currComments['columns']) || !empty($currComments['table']))){
					$commentsAll[$tablename] = array();
					$commentsAll[$tablename]=$currComments;
		        }
				$processed_tables[] = $tablename;
		   }
		}
//print_r($commentsAll);

      $comments_sql = '';
      foreach($commentsAll as $tableName=>$tabArray){
      	$search = "CREATE TABLE {$tableName} (";
  		if($tableName == 'does_not_exist') {
                continue; // support new vardef definitions
        }
        $debugTable = false;
        if ( $tableName == 'contracts' ) {
            $debugTable = true;
        }

        if ( $debugTable ) { echo("$tableName<br>\n\n"); }
        // echo '***table name here ***'.$tableName;

      	if(isset($tabArray) && $tabArray != null){
            $currTable_Orig = $currTable = $sqlArray[$tableName];
            unset($sqlArray[$tableName]);
            if ( $debugTable ) { echo(__LINE__.": currTable_Orig=$currTable_Orig<br>"); }

            // We need to extract the data columns from the indexes.
            $currTableColumns = substr($currTable,stripos($currTable,'(')+1);
            $currTableColumns = substr($currTableColumns,0,strripos($currTableColumns,')'));
            $startOfIndexes = strlen($currTableColumns);
            $checkIndexStarts = array(' PRIMARY KEY',',PRIMARY KEY',' KEY',',KEY',' FOREIGN KEY',',FOREIGN KEY');
            foreach ( $checkIndexStarts as $checkString ) {
                if ( ($thisPos = strpos($currTableColumns,$checkString)) !== FALSE ) {
                    $startOfIndexes = min($startOfIndexes,$thisPos);
                }
            }
            $indexes = substr($currTableColumns,$startOfIndexes);
            if ( $debugTable ) { echo(__LINE__.": indexes=$indexes<br>"); }
            $currTableColumns = substr($currTableColumns,0,$startOfIndexes-1);
            if ( $debugTable ) { echo(__LINE__.": currTableColumns before: $currTableColumns<br>"); }
            // This fixes up the decimal(26,6) things, so they don't try to get split off into separate columns.
            $currTableColumns = preg_replace('/\(([^)]*),([^)]*)\)/','(\1^^^\2)',$currTableColumns);
            // This one replaces the DEFAULT ',' in import_maps
            $currTableColumns = str_replace("','","'^^^'",$currTableColumns);
            if ( $debugTable ) { echo(__LINE__.": currTableColumns after: $currTableColumns<br>"); }

            //$currTable= substr($currTable,stripos($currTable,'(')+1,strripos($currTable,')')-1 );
            $currTableDataColumnsTmp = explode(',',$currTableColumns);
            $currTableDataColumns = array();
            foreach ( $currTableDataColumnsTmp as $col ) {
                if ( !empty($col) ) {
                    $currTableDataColumns[] = str_replace('^^^',',',$col);
                }
            }
            if ( $debugTable ) { echo(__LINE__.": currTableDataColumns=<pre>\n".print_r($currTableDataColumns,true)."</pre><br>"); }

            $currTableSql_comments = "CREATE TABLE {$tableName} (";

            if(isset($tabArray['table'])&& $tabArray['table'] != null){
                $comments_sql .="ALTER TABLE {$tableName} COMMENT '".addslashes($tabArray['table'])."';"."\n";
            }
            foreach($currTableDataColumns as $currColumn){
                $col = explode(' ',trim($currColumn));
                $colName = trim($col[0]);
                if ( empty($colName) ) {
                    continue;
                }
                //if($tableName == 'accounts') print_r($colName).'</br>';
                if(isset($tabArray['columns'][$colName]) && $tabArray['columns'][$colName] != null){

                    //get the column comment
                    $colComment = $tabArray['columns'][$colName];
                    //add to the column an dprepare alter statement
                    $comments_sql .="ALTER TABLE {$tableName} MODIFY COLUMN $currColumn COMMENT '".addslashes($colComment)."';"."\n";
                    $currTableSql_comments .= $currColumn.' '." COMMENT '".addslashes($colComment)."' ,";
                }
                else{
                    $currTableSql_comments .= $currColumn." ,";
                }
            }
            $currTableSql_comments = rtrim($currTableSql_comments,', ');
            if ( !empty($indexes) ) {
                $currTableSql_comments .= ',    '.$indexes;
            }
            $currTableSql_comments .= ' )';

            if(isset($tabArray['table'])&& $tabArray['table'] != null){
                $currTableSql_comments .= " COMMENT '{$tabArray['table']}'";
            }

            if ( $debugTable ) { echo($currTableSql_comments); }
            $sql .= $currTableSql_comments.";\n";
        }
      }
      $sql .= implode(";\n",$sqlArray);

      //echo '**************************COMMENTS***************************';
      //print_r($comments_er);
		$schema_dir =sugar_cached("erschema");
        mkdir_recursive($schema_dir);
	 	$schema_file =$schema_dir.'/schema.sql';
	 	$fk_schema_file =$schema_dir.'/fkschema.sql';
	 	$comments_schema_file =$schema_dir.'/comments.sql';

		if(file_exists($fk_schema_file)) {
			unlink($fk_schema_file);
		}
        global $mod_strings;
        if(!file_exists($fk_schema_file)) {
			if(function_exists('sugar_fopen')){
				$fp = @sugar_fopen($fk_schema_file, 'w+'); // attempts to create file
		     }
		     else{
				$fp = fopen($fk_schema_file, 'w+'); // attempts to create file
		     }
			if(!is_resource($fp)) {
				$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
			}
		}

        if(@fwrite($fp, $sql_fk) === false) {
				$GLOBALS['log']->fatal('UpgradeWizard could not write to upgradeWizard.log');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
		 }

		if(file_exists($schema_file)) {
			unlink($schema_file);
		}
		if(!file_exists($schema_file)) {
			if(function_exists('sugar_fopen')){
				$fp = @sugar_fopen($schema_file, 'w+'); // attempts to create file
		     }
		     else{
				$fp = fopen($schema_file, 'w+'); // attempts to create file
		     }
			if(!is_resource($fp)) {
				$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
			}
		}
		else{
			unlink($schema_file);
			if(function_exists('sugar_fopen')){
				$fp = @sugar_fopen($schema_file, 'w+'); // attempts to create file
		     }
		     else{
				$fp = fopen($schema_file, 'w+'); // attempts to create file
		     }
			if(!is_resource($fp)) {
				$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
			}
		}

	    if(@fwrite($fp, $sql) === false) {
				$GLOBALS['log']->fatal('UpgradeWizard could not write to upgradeWizard.log');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
		 }
		if(file_exists($comments_schema_file)) {
			unlink($comments_schema_file);
		}
		if(!file_exists($comments_schema_file)) {
			if(function_exists('sugar_fopen')){
				$fp = @sugar_fopen($comments_schema_file, 'w+'); // attempts to create file
		     }
		     else{
				$fp = fopen($comments_schema_file, 'w+'); // attempts to create file
		     }
			if(!is_resource($fp)) {
				$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
			}
		}
		else{
			unlink($comments_schema_file);
			if(function_exists('sugar_fopen')){
				$fp = @sugar_fopen($comments_schema_file, 'w+'); // attempts to create file
		     }
		     else{
				$fp = fopen($comments_schema_file, 'w+'); // attempts to create file
		     }
			if(!is_resource($fp)) {
				$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
			}
		}

	    if(@fwrite($fp, $comments_sql) === false) {
				$GLOBALS['log']->fatal('UpgradeWizard could not write to upgradeWizard.log');
				die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
		 }

		 $c=0;
foreach($processed_tables as $p){
	//echo '**** '.$c.$p;
	$c= $c+1;
}
//print_r($commentsAll['campaigns']);
/*
foreach($commentsAll as $k=>$v){

	echo '********** '.$c.' '.$k.'</br>';
	$c=$c+1;
}
*/
	if($execute){
		$response = createDatabaseForER_Diagram($sql);
		//echo 'comes here *********** ';
		/*
		global $dbinstances;
		print_r($dbinstances);
	    $db =$dbinstances['er_diagram'];
	    echo 'comes here ****GGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGGG******* ';
	    print_r($db);
        $q = "CREATE TABLE relationships (id char(36) NOT NULL ,relationship_name varchar(150) NOT NULL ,lhs_module varchar(100) NOT NULL ,lhs_table varchar(64) NOT NULL ,lhs_key varchar(64) NOT NULL ,rhs_module varchar(100) NOT NULL ,rhs_table varchar(64) NOT NULL ,rhs_key varchar(64) NOT NULL ,join_table varchar(64) NULL ,join_key_lhs varchar(64) NULL ,join_key_rhs varchar(64) NULL ,relationship_type varchar(64) NULL ,relationship_role_column varchar(64) NULL ,relationship_role_column_value varchar(50) NULL ,reverse bool DEFAULT '0' NULL ,deleted bool DEFAULT '0' NULL , PRIMARY KEY (id), KEY idx_rel_name (relationship_name)) CHARACTER SET utf8 COLLATE utf8_general_ci";
        $db->query($q,true);
         die();
		//$db = & DBManagerFactory::getInstance('er_diagram');



		$queArray = array();
		$queArray = explode(";",$sql);
		print_r($db);
		//print_r($queArray);
        $count = 0;

		foreach($queArray as $query){
			$q = '';
			$count++;
			$q=str_replace(';','',$query);
			print_r($query).'</br>';
			echo $count.'</br>';
			echo '</br>';
			$db->query($query,true);
			$query = '';
		}
		*/
	}
	 if($return){
	 	return $sql;
	 }
}

////GENERATE SCHEMA FOR ER DIAGRAM/////////////
//////////////////////////////////////////////
createSchema(true,false,$response);


//////////////////////////////////////////////
//////////////////////////////////////////////
/*
function createTable(SugarBean $bean,$execute=false)
{
	$sql = $this->getHelper()->createTableSQL($bean);
	$this->tableName = $bean->getTableName();
	$msg = "Error creating table: ".$this->tableName. ":";
	$this->query($sql,true,$msg);
}

/**
 * Implements creation of a db table
 *
 * @param string $tablename
 * @param array  $fieldDefs
 * @param array  $indices
 * @param string $engine    MySQL engine to use
 */
 /*
function createTableParams($tablename,$fieldDefs,$indices,$engine = null,$execute=false)
{
	if (!empty($fieldDefs)) {
	    $sql = $this->getHelper()
	                ->createTableSQLParams($tablename, $fieldDefs, $indices,$engine);
	    $this->tableName = $tablename;
	    if ($sql) {
	        $msg = "Error creating table: ".$this->tableName. ":";
	        $this->query($sql,true,$msg);
	    }
	}
}
*/
/*
$newUWMsg =<<<eoq
<div id='er_schema' name='er_schema' style='display:none'>
<table cellpadding="3" cellspacing="0" border="0">
	<tr>
		<th colspan="2" align="center">
			<h1><span class='error'><b>************************************************************************</b></span></h1>
			<span class='error'><b>DDL files for ER Diagram Schema and FK schema have been generated</b></span>

			<h1><span class='error'><b><a href=cache/erschema/schema.sql>Download Complete ER Diagram Schema DDL File</a></b></span></h1>
			</br>
			<h1><span class='error'><b><a href=cache/erschema/fkschema.sql>Download Foreign Keys Schema DDL File</a></b></span></h1>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
			<h1><span class='error'><b><a href=cache/erschema/comments.sql>Download Schema Comments DDL File</a></b></span></h1>
			<h1><span class='error'><b>************************************************************************</b></span></h1>
		</th>
	</tr>
</table>
</div>
eoq;
*/
//echo $newUWMsg;
//$response  ='success';
if (!empty($response)) {
	echo $response;
}
sugar_cleanup();
exit();

?>
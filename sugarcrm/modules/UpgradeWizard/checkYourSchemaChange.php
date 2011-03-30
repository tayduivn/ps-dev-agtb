<?php
//FILE SUGARCRM flav=int ONLY
///////////////////////////////////////////////////////////////////////////////
////	STANDARD REQUIRED SUGAR INCLUDES AND PRESETS
if(!defined('sugarEntry')) define('sugarEntry', true);

require_once('include/JSON.php');

$tabDropDown = "<option value=Select A Table>Select A Table";
foreach(getAllTables() as $tab){
	$tabDropDown .="<option value={$tab}>{$tab}";
}

$colsDropDown = '';
echo "<script> function populateTableColumns(){
	 var callback = {
	     success:function(r) {
		   //rebuild the tree to display the latest node
		  //alert(r.responseText);
		  //SUGAR.util.evalScript(r.responseText);
		  //document.getElementById('select_column').innerHTML=r.responseText;
          //alert(document.getElementById('tabColumns').childNodes[0].innerHTML);
		   //var opt = document.createElement('select');
			//opt.innerHTML =  r.responseText;
			//opt.value =  r.responseText;
			//alert(opt.innerHTML);
			//document.getElementByID('tabColumns').appendChild(opt);
		   //SUGAR.util.evalScript(document.getElementById('tabColumns').appendChild(opt));
		   SUGAR.util.evalScript(document.getElementById('column_id').innerHTML=r.responseText);
	     }
	}
	var selectedTable = document.getElementById('table_id').value;
	postData = 'selectedTable=' + selectedTable+ '&module=Administration&action=populateColumns&to_pdf=1';
	YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
}

function getTable(){
	alert(document.getElementById('tabColumns'));
}</script>";


echo "<form name='checkSchema'>
<table border='1' cellpadding='0' cellspacing='0' width='100%'>
	<tr>
		<td class='dataField' width='20%' <b>Tables</b>
			<div id='tabs' name='tabs'>
			<table>
				<tr>
					<span sugar='slot9b'>&nbsp;<select tabindex='1' onchange='populateTableColumns();' name='table_name' id='table_id'>$tabDropDown</select>
	   				</span sugar='slot'>
	   			</tr>
	   		</table>
	   		</div>
		</td>
		<td class='dataField' width='20%' <b>Columns</b>
			<div id='tabColumns' name='tabColumns'>
			       <table>
				      <tbody> <tr><td>
					   		<span sugar='slot9b'>&nbsp;<select tabindex='1' name='column_name' id='column_id'></select>
	   						</span sugar='slot'>
			           </td></tr></tbody>
			       </table>
			  </div>
		</td>
		<td class='dataField' width='10%' <b>Check Schema</b>
			<div id='checkS' name='checkS'>
			       <table>
				      <tbody> <tr><td>
					   		<span sugar='slot9b'>&nbsp;<input type='submit' class='button' value='".$mod_strings['LBL_GO']."'>
	   						</span sugar='slot'>
			           </td></tr></tbody>
			       </table>
			  </div>
		</td>
		<td class='dataField' width='30%' <b>Schema Results</b>
			<div id='checkS' name='checkS'>
			       <table>
				      <tbody> <tr><td>

			           </td></tr></tbody>
			       </table>
			  </div>
		</td>
	</tr>
</table>
</form>";

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


    echo "{$mod_strings['LBL_PERFORM_CREATE_DB_1']} {$setup_db_database_name} {$mod_strings['LBL_PERFORM_CREATE_DB_2']} {$setup_db_host_name}...";

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
				$queArray = explode(";",$sql);
				$count = 0;
                foreach($queArray as $query){
					$q = '';
					$count++;
					$q=str_replace(';','',$query);
					//print_r($q).'</br>';
					echo $count.'</br>';
					echo '</br>';
					//$q = "CREATE TABLE relationships (id char(36) NOT NULL ,relationship_name varchar(150) NOT NULL ,lhs_module varchar(100) NOT NULL ,lhs_table varchar(64) NOT NULL ,lhs_key varchar(64) NOT NULL ,rhs_module varchar(100) NOT NULL ,rhs_table varchar(64) NOT NULL ,rhs_key varchar(64) NOT NULL ,join_table varchar(64) NULL ,join_key_lhs varchar(64) NULL ,join_key_rhs varchar(64) NULL ,relationship_type varchar(64) NULL ,relationship_role_column varchar(64) NULL ,relationship_role_column_value varchar(50) NULL ,reverse bool DEFAULT '0' NULL ,deleted bool DEFAULT '0' NULL , PRIMARY KEY (id), KEY idx_rel_name (relationship_name)) CHARACTER SET utf8 COLLATE utf8_general_ci";
					mysql_query($q,$link);
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
}

function checkRelationshipOneToMany($relationship,$table,$rel_key,&$col_data_type,&$is_col_PK){
	if(!checkTableExists($table)) {
		$_SESSION['dbScanError']['high'][] = ' Missing table '.strtoUpper($table).' but used in relationship '.strtoUpper($relationship);
	}
	else{
		checkColumnKeysIndices($relationship,$table,$rel_key,$col_data_type,$is_col_PK);
	}
}

function checkRelationshipManyToMany($relationship,$join_table,$join_lhs_rel_key,$join_rhs_rel_key,&$join_lhs_col_data_type,&$join_rhs_col_data_type,&$is_join_lhs_col_PK,&$is_join_rhs_col_PK){
	if(!checkTableExists($join_table)) {
		$_SESSION['dbScanError']['high'][] = ' Missing Join table '.strtoUpper($join_table).' but used in relationship '.strtoUpper($relationship);
	}
	else{
		checkColumnKeysIndices($relationship,$join_table,$join_lhs_rel_key,$join_lhs_col_data_type,$is_join_lhs_col_PK);
		checkColumnKeysIndices($relationship,$join_table,$join_rhs_rel_key,$join_rhs_col_data_type,$is_join_rhs_col_PK);
	}
}

function checkColumnKeysIndices($relationship,$table,$rel_key,&$col_data_type,&$is_col_PK){
	$col = checkColumnExists($table,$rel_key);
	if($col['column_name']==null || empty($col['column_name'])){
		$_SESSION['dbScanError']['high'][] = ' Missing Column '.strtoUpper($rel_key).' in Table '.strtoUpper($table).' but used in relationship '.strtoUpper($relationship);
	}
	else{
		$col_data_type= $col['data_type'];
	}
	if($col['column_name'] != null && checkIndexExists($table,$col['column_name']) == null){
		$_SESSION['dbScanError']['medium'][] = ' Missing index on Column '.strtoUpper($col['column_name']).' in Table '.strtoUpper($table).' but used in relationship '.strtoUpper($relationship);
	}
	elseif(checkIndexExists($table,$col['column_name']) != null){
		$col_indcies = checkIndexExists($table,$col['column_name']);
		if(is_array($col_indcies)){
			foreach($col_indcies as $index_curr){
				if($index_curr == 'PRIMARY'){
					$is_col_PK = true;
				}
			}
		}
	}
}

function checkSchema($execute=false,$return=false){


	global $current_user, $beanFiles;
	global $dictionary;
	set_time_limit(3600);

	$db = &DBManagerFactory::getInstance();
	foreach( $beanFiles as $bean => $file ){
    	require_once( $file );
	}
	VardefManager::clearVardef();
	$FK_ARRAY = array();
	$dictionary = $GLOBALS['dictionary'];
	// add non-module Beans to this array to keep the installer from erroring.
	$nonStandardModules = array (
	    //'Tracker',
	);
    $_SESSION['dbScanError'] = array();
    $dbScanSuccess = '';
    $que = "select * from relationships";
    $query = $db->query($que);
    $sql_fk = '';
   	while($rel_def = $db->fetchByAssoc($query))
	   	    {
          if($rel_def['relationship_type']== 'one-to-many'){
              $rhs_col_data_type='';
			  $lhs_col_data_type='';
			  $is_rhs_col_PK =false;
			  $is_lhs_col_PK =false;

			  $dbScanSuccess[]=$rel_def['relationship_name'];
              if($rel_def['rhs_table'] != null){
					checkRelationshipOneToMany($rel_def['relationship_name'],$rel_def['rhs_table'],$rel_def['rhs_key'],$rhs_col_data_type,$is_rhs_col_PK);
              }
              else{
              		$_SESSION['dbScanError']['high'][] = ' RHS table not defined for relationship '.strtoUpper($rel_def['relationship_name']);
              }

              if($rel_def['lhs_table'] != null){
					checkRelationshipOneToMany($rel_def['relationship_name'],$rel_def['lhs_table'],$rel_def['lhs_key'],$lhs_col_data_type,$is_lhs_col_PK);
					//check data type primary key etc...
					if($rhs_col_data_type != null && $lhs_col_data_type != null){
						if(trim($rhs_col_data_type) != trim($lhs_col_data_type)){
							$_SESSION['dbScanError']['low'][] = ' Data type for Column '.strtoUpper($rel_def['rhs_key']).' in Table '.strtoUpper($rel_def['rhs_table']).' is different than Data type for Column '.strtoUpper($rel_def['lhs_key']).'  in Table '.strtoUpper($rel_def['lhs_table']).'. These columns are used in relationship '.strtoUpper($rel_def['relationship_name']);
						}
					}
					if($is_rhs_col_PK && $is_lhs_col_PK){
						$_SESSION['dbScanError']['high'][] = ' Column '.strtoUpper($rel_def['rhs_key']).' in Table '.strtoUpper($rel_def['rhs_table']).' and Column '.strtoUpper($rel_def['lhs_key']).' in Table '.strtoUpper($rel_def['lhs_table']).' both have PRIMARY KEY and used in relationship '.strtoUpper($rel_def['relationship_name']);
					}
            	}
            	else{
              		$_SESSION['dbScanError']['high'][] = ' LHS table not defined for relationship '.strtoUpper($rel_def['relationship_name']);
              	}
          }

            if($rel_def['relationship_type']== 'many-to-many'){
	          	$join_lhs_col_data_type='';
			  	$join_rhs_col_data_type='';
			  	$is_join_lhs_col_PK =false;
			  	$is_join_rhs_col_PK =false;
			  	$rhs_col_data_type='';
			  	$lhs_col_data_type='';
			  	$is_rhs_col_PK =false;
			  	$is_lhs_col_PK =false;

				if($rel_def['join_table'] != null){
		          	$rel_def['join_table'];
		          	$rel_def['join_key_lhs'];
		          	checkRelationshipManyToMany($rel_def['relationship_name'],$rel_def['join_table'],$rel_def['join_key_lhs'],$rel_def['join_key_rhs'],$join_lhs_col_data_type,$join_rhs_col_data_type,$is_join_lhs_col_PK,$is_join_rhs_col_PK);
				 }
	            else{
	              	$_SESSION['dbScanError']['high'][] = ' JOIN table not defined for relationship '.strtoUpper($rel_def['relationship_name']);
	             }
            	 //Check LHS Table for many-to-many relationship
            	 if($rel_def['lhs_table'] != null){
            		checkRelationshipOneToMany($rel_def['relationship_name'],$rel_def['lhs_table'],$rel_def['lhs_key'],$lhs_col_data_type,$is_lhs_col_PK);
            		//check data type primary key etc...
					if($join_lhs_col_data_type != null && $lhs_col_data_type != null){
						if(trim($join_lhs_col_data_type) != trim($lhs_col_data_type)){
							$_SESSION['dbScanError']['low'][] = ' Data type for Column '.strtoUpper($rel_def['lhs_key']).' in Table '.strtoUpper($rel_def['lhs_table']).' is different than Data type for Column '.strtoUpper($rel_def['join_key_lhs']).'  in Table '.strtoUpper($rel_def['join_table']).'. These columns are used in relationship '.strtoUpper($rel_def['relationship_name']);
						}
					}
					if($is_join_lhs_col_PK && $is_lhs_col_PK){
						$_SESSION['dbScanError']['high'][] = ' Column '.strtoUpper($rel_def['lhs_key']).' in Table '.strtoUpper($rel_def['lhs_table']).' and Column '.strtoUpper($rel_def['join_key_lhs']).' in Table '.strtoUpper($rel_def['join_table']).' both have PRIMARY KEY and used in relationship '.strtoUpper($rel_def['relationship_name']);
					}
            	 }
            	 else{
            	 	$_SESSION['dbScanError']['high'][] = ' LHS table not defined for relationship '.strtoUpper($rel_def['relationship_name']);
            	 }

            	  //Check RHS Table for many-to-many relationship
            	 if($rel_def['rhs_table'] != null){
            		checkRelationshipOneToMany($rel_def['relationship_name'],$rel_def['rhs_table'],$rel_def['rhs_key'],$rhs_col_data_type,$is_rhs_col_PK);
            		//check data type primary key etc...
					if($join_rhs_col_data_type != null && $rhs_col_data_type != null){
						if(trim($join_rhs_col_data_type) != trim($rhs_col_data_type)){
							$_SESSION['dbScanError']['low'][] = ' Data type for Column '.strtoUpper($rel_def['rhs_key']).' in Table '.strtoUpper($rel_def['rhs_table']).' is different than Data type for Column '.strtoUpper($rel_def['join_key_rhs']).'  in Table '.strtoUpper($rel_def['join_table']).'. These columns are used in relationship '.strtoUpper($rel_def['relationship_name']);
						}
					}
					if($is_join_rhs_col_PK && $is_rhs_col_PK){
						$_SESSION['dbScanError']['high'][] = ' Column '.strtoUpper($rel_def['rhs_key']).' in Table '.strtoUpper($rel_def['rhs_table']).' and Column '.strtoUpper($rel_def['join_key_rhs']).' in Table '.strtoUpper($rel_def['join_table']).' both have PRIMARY KEY and used in relationship '.strtoUpper($rel_def['relationship_name']);
					}
            	 }
            	 else{
					$_SESSION['dbScanError']['high'][] = ' RHS table not defined for relationship '.strtoUpper($rel_def['relationship_name']);
            	 }
            }
	    }
	    $db_scan = '';
	if(isset($_SESSION['dbScanError']) && $_SESSION['dbScanError'] != null){
		$db_scan .= "**************************** FOLLOWING SCHEMA INCONSITENCIES EXIST IN DATABASE *********************"."\n";
		$db_scan .= "\n";
	}
	if(isset($_SESSION['dbScanError']['high']) && $_SESSION['dbScanError']['high'] != null){
		$db_scan .="***************************************************************************************************"."\n";
		$db_scan .="*******************SEVERITY HIGH (SCHEMA INCONSISTENCIES)*******************************************"."\n";
		$db_scan .= "\n";
		foreach($_SESSION['dbScanError']['high'] as $dbEr){
			$db_scan .= $dbEr."\n";
		}
		$db_scan .= "**************************************************************************************************"."\n";
		$db_scan .= "\n";
	}
	if(isset($_SESSION['dbScanError']['medium']) && $_SESSION['dbScanError']['medium'] != null){
		$db_scan .= "***************************************************************************************************"."\n";
		$db_scan .= "*******************SEVERITY MEDIUM (SCHEMA INCONSISTENCIES)****************************************"."\n";
		$db_scan .= "\n";
		foreach($_SESSION['dbScanError']['medium'] as $dbEr){
			$db_scan .= $dbEr."\n";
		}
		$db_scan .= "***************************************************************************************************"."\n";
		$db_scan .= "\n";

	}
	if(isset($_SESSION['dbScanError']['low']) && $_SESSION['dbScanError']['low'] != null){
		$db_scan .= "***************************************************************************************************"."\n";
		$db_scan .= "*******************SEVERITY LOW (SCHEMA INCONSISTENCIES)*******************************************"."\n";
		$db_scan .= "\n";
		foreach($_SESSION['dbScanError']['low'] as $dbEr){
			$db_scan .= $dbEr."\n";
		}
		$db_scan .= "****************************************************************************************************"."\n";
	}

	$cwd = getcwd();

	mkdir_recursive(clean_path("{$cwd}/{$GLOBALS['sugar_config']['upload_dir']}dbscan"));
	$dbscan_dir =clean_path("{$cwd}/{$GLOBALS['sugar_config']['upload_dir']}dbscan");
	$dbscan_file =$dbscan_dir.'/schema_inconsistencies.txt';
	//$fk_schema_file =$schema_dir.'/fkschema.sql';
	if(file_exists($dbscan_file)) {
		unlink($dbscan_file);
	}
    global $mod_strings;
	if(!file_exists($dbscan_file)) {
		if(function_exists('sugar_fopen')){
			$fp = @sugar_fopen($dbscan_file, 'w+'); // attempts to create file
		 }
		 else{
			$fp = fopen($dbscan_file, 'w+'); // attempts to create file
		 }
		if(!is_resource($fp)) {
			$GLOBALS['log']->fatal('UpgradeWizard could not create the upgradeWizard.log file');
			die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
		}
	}

	if(@fwrite($fp, $db_scan) === false) {
			$GLOBALS['log']->fatal('UpgradeWizard could not write to upgradeWizard.log');
			die($mod_strings['ERR_UW_LOG_FILE_UNWRITABLE']);
	 }
}

////GENERATE SCHEMA FOR ER DIAGRAM/////////////
//////////////////////////////////////////////
//checkSchema();



function getAllTables(){
	global $sugar_config;
	global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
	$db = &DBManagerFactory::getInstance('information_schema');
	$db_name= $sugar_config['dbconfig']['db_name'];
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];
    $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
    mysql_select_db('information_schema');
    $qu="SELECT table_name FROM information_schema.tables where table_schema='trunk'";
	$ct =mysql_query($qu,$link);
    //$row=mysql_fetch_assoc($ct);
    $tables= array();
    while($row = $db->fetchByAssoc($ct)){
    	$tables[]=$row['table_name'];
    }
	return $tables;
}

function tableColumns(&$colsDrop,$table_name){

	global $sugar_config;
	global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
	$db = &DBManagerFactory::getInstance('information_schema');
	$db_name= $sugar_config['dbconfig']['db_name'];
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];
    $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
    mysql_select_db('information_schema');
    $qu="SELECT column_name FROM information_schema.columns WHERE table_schema = '".$db_name."' AND table_name = '".$table_name;
	$ct =mysql_query($qu,$link);
    //$cols= '';
    while($row = $db->fetchByAssoc($ct)){
    	 $colsDrop[] =$row['column_name'];
    }
    print_r('***************** '.$colsDrop);
	//return  $cols;
}
///////////////////////////////////////////////////////////////
////FUNCTIONS for checking table, column, index, data type/////
function checkTableExists($table_name){
	global $sugar_config;
	global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
	$db = &DBManagerFactory::getInstance('information_schema');
	$db_name= $sugar_config['dbconfig']['db_name'];
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];
    $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
    //$sql = "SHOW DATABASES";
    //$result = mysql_query($sql,$link);
    //$dblist = mysql_fetch_array($result, MYSQLI_NUM);

    //$c = 0;
    //while (count($dblist) > $c) {
	    //print_r($dblist[$c] . "<br>\n");
    mysql_select_db('information_schema');
    $qu="SELECT count(*) FROM information_schema.tables WHERE table_schema = '".$db_name."' AND table_name = '".$table_name."'";
	$ct =mysql_query($qu,$link);
    $row=mysql_fetch_assoc($ct);
    //print_r($row);

	    //$qr="SELECT count(*) FROM ".$dblist[$c];

	    //print_r($con($qr));
	    //$result  = NULL;
	    //mysql_free_result($result);
	    //$query="SELECT count(*)	FROM information_schema.tables WHERE table_schema = ".$db_name." AND table_name = ".$table_name;
	    //$q= mysql_query($query, $link);
        //echo(mysql_query($query,$link));
	    //$c++;
   //}


	//$query="SELECT * FROM accounts";//".tables WHERE table_schema = ".$db_name;
	//mysql_select_db($dblist[$c]);
    //$res = mysql_query($query, $link);
    //echo mysql_query($res, $link);
	//$result = $db->query($query);
	//$row = $db->fetchByAssoc($result);
	//echo $row;

	if($row['count(*)']>0){
		//echo 'TABLE TRUE ***************'.$row['count(*)'].'</br>';
		return true;
	}
	return false;
}
function checkColumnExists($table_name,$column_name){
	global $sugar_config;
	global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
	$db = &DBManagerFactory::getInstance('information_schema');
	$db_name= $sugar_config['dbconfig']['db_name'];
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];
    $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
    mysql_select_db('information_schema');
    $qu="SELECT column_name,data_type FROM information_schema.columns WHERE table_schema = '".$db_name."' AND table_name = '".$table_name."' AND column_name='".$column_name."'";
	$ct =mysql_query($qu,$link);
    $row=mysql_fetch_assoc($ct);
	return $row;
}
function checkIndexExists($table_name,$column_name){
	global $sugar_config;
	global $setup_db_database_name;
    global $setup_db_host_name;
    global $setup_db_host_instance;
    global $setup_db_admin_user_name;
    global $setup_db_admin_password;
	$db = &DBManagerFactory::getInstance('information_schema');
	$db_name= $sugar_config['dbconfig']['db_name'];
	$setup_db_host_name = $sugar_config['dbconfig']['db_host_name'];
  	$setup_db_admin_user_name = $sugar_config['dbconfig']['db_user_name'];
    $setup_db_host_instance = $sugar_config['dbconfig']['db_host_instance'];
    $setup_db_admin_password = $sugar_config['dbconfig']['db_password'];
    $link = @mysql_connect($setup_db_host_name, $setup_db_admin_user_name, $setup_db_admin_password);
    mysql_select_db('information_schema');
    $qu="SELECT index_name FROM information_schema.statistics WHERE table_schema = '".$db_name."' AND table_name = '".$table_name."' AND column_name='".$column_name."'";
	$ct =mysql_query($qu,$link);
    $row=mysql_fetch_assoc($ct);
	return $row;
}

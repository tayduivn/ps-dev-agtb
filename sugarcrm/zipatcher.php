<?php
define('sugarEntry', true);
//FILE SUGARCRM flav=int ONLY
?>
<script>


function module_select() {

	themanifest=document.getElementById('manifest');
	
	if (themanifest.options[themanifest.selectedIndex].value =='module') {
		document.getElementById("manifestspan").style.display = "inline";
	} else {
		document.getElementById("manifestspan").style.display = "none	";
	}
			
}

function filesource_select() {
	filesource=document.getElementById('filesource');
	
	if (filesource.options[filesource.selectedIndex].value =='zip') {
		document.getElementById("zipspan").style.display = "inline";
	} else {
		document.getElementById("zipspan").style.display = "none	";
	}	
}
</script>
<form method='post' name="theform" action='zipatcher.php'>
File Source:<select id='filesource' name='filesource' onchange='filesource_select();'>
� <option selected='selected' value="current">This directory</option>
� <option value="zip">Zip</option>
</select>
<span id='zipspan' style="display:none">
<br/>
Enter zip file name:<input type='text' name='zip_name'>&nbsp;<span>(Place the zip fie in the current directory.)</span>
</span>
<br/>
<br/>
<div>Provide name of files to be included in patch, include full path</div>
<textarea name='filestopatch' cols='60' rows='10'>
</textarea>
<BR>
<font size = '2' color='Maroon'>
Installable Patch:<select id='manifest' name='manifest' onchange='module_select();'>
� <option selected='selected' value="patch">Patch</option>
� <option value="module">Module</option>
</select>&nbsp;Uninstallable:<input type ='checkbox' name='is_uninstallable' disabled>&nbsp;<input type='submit' value='Create Patch'><BR>

<span id='manifestspan' style="display:none">
<HR>
Manifest Name:<input type='text' name='patch_name' value='Sugar Internal Only'>&nbsp;&nbsp;Description:<input type='text' name='patch_description' size='30' value='For Use By Sugar CRM employees ONLY'>
</span>
</font>
<BR>
<br>
</form>

<?php
define('sugarEntry', true);

require_once('include/utils.php');
require_once('include/utils/file_utils.php');
require_once('include/dir_inc.php');
require_once('include/utils/zip_utils.php');
$dir='';
$dirsecond='';
$date = date('Y_m_d_His');
$unzip_dir='';
$source_dir='./';

function createpatchdir( $type='patch') {
	global $dir,$dirsecond,$date;
	
	
	if(!file_exists('zipatches'))
    	sugar_mkdir('zipatches');

	$dir = 'zipatches/' . $date ;
	if(!file_exists($dir))
		sugar_mkdir($dir);
		
	if ($type=='patch') {	
		
	 	$dirsecond = '/SugarPatch/';
    	if(!file_exists($dir. $dirsecond))
      		sugar_mkdir($dir.$dirsecond);
	}
}


function copyfiles($type='patch') {
	global $dir,$dirsecond,$source_dir;
	
	$files = explode("\n",$_POST['filestopatch']);
	
	$target=$dir;
	if ($type=='patch') $target.='/'.$dirsecond;
	
	foreach($files as $filepath){
		if (strlen(trim($filepath)) > 0) { 
	    	$filepath = trim($filepath);
	    	$filepath  = str_replace('\\', '/', $filepath);
	    	mkdir_recursive($target.'/'. dirname($filepath), true);
	    	copy_recursive($source_dir.$filepath, $target.'/'. $filepath);
  		}
  	}	
}

function createInstallDefs(){
	
	$files = explode("\n",$_POST['filestopatch']);
	
	$installdefs['id'] = 'sugar' . mt_rand(1000, 9000);
	foreach($files as $filepath){
		if (strlen(trim($filepath)) > 0) { 
	    	$filepath = trim($filepath);
	    	$filepath  = str_replace('\\', '/', $filepath);
	    	
	    	$installdefs['copy'][] = array('from'=>'<basepath>/'. $filepath, 'to'=>$filepath); 
  		}
  	}	
  	
  	return '$installdefs =' . var_export($installdefs,true) .';';
}

function createManifest($name, $description, $type='patch', $is_uninstallable=true){
    
	$date = date('Y-m-d H:i:s');
	$time = time();
	$is_uninstallable = ($is_uninstallable ? 'true' : 'false');
	
	$manifest['acceptable_sugar_versions']=array();
	$manifest['author']='SugarCRM, Inc.';
	$manifest['description']="$description";
	$manifest['icon']="";
	$manifest['is_uninstallable']=$is_uninstallable;
	$manifest['name']="$name";
	$manifest['published_date']="$date";
	$manifest['type']="$type";
	$manifest['version']="$time";
	
	if ($type == 'patch') {
		$manifest['copy_files']= array ('from_dir' => 'SugarPatch', 'to_dir' => '','force_copy' => array());
	}
	
	return '$manifest =' . var_export($manifest,true) . ';';
}
//<?php hack for eclipse text highlighting

if(!empty($_POST['filestopatch'])){
  
	if (!empty($_POST['filesource']) and $_POST['filesource']=='zip' ) {
		echo "<br/>Processing zip file....";
		
		$unzip_dir=sys_get_temp_dir(). 'patch' . mt_rand(1000, 9000);
		sugar_mkdir($unzip_dir);
	
		if (empty($_POST['zip_name'])) {
			die('A zip name is required.');
		}
		
		echo "<br/>Unzipping conents...";
		unzip(dirname(__FILE__).'/'.$_POST['zip_name'],$unzip_dir);
		
	 	$zipcontents=scandir($unzip_dir);
	 	foreach ($zipcontents as $sugardirname ) {
	 		if ($sugardirname != '.' and $sugardirname!= '..') {
	 			$source_dir=$unzip_dir.='/'.$sugardirname . '/';
	 			break;
	 		}
	 		
	 	}
	 	echo "<br/>Zip source." . $source_dir;
		
	}  
	echo "<br/>Creating the patch directory.";
	createpatchdir($_POST['manifest']);
	
	echo "<br/>Copying files..";
	copyfiles($_POST['manifest']);
  
	if(!empty($_REQUEST['patch_name'])){
		$patch_name = $_REQUEST['patch_name'];
	} else{
		$patch_name = 'Sugar Internal Only';    
	}
    
	if(!empty($_REQUEST['patch_description'])){
		$patch_description = $_REQUEST['patch_description'];
	} else {
		$patch_description = 'For Use By Sugar CRM employees ONLY';
	}
    
	$is_uninstallable = (isset($_POST['is_uninstallable']) && $_POST['is_uninstallable'] == "on" ? true :false);
  
	 $fp = sugar_fopen($dir . '/manifest.php', 'w');
	 fwrite($fp, "<?php\n");
	 fwrite($fp, createManifest($patch_name, $patch_description, $_POST['manifest'], $is_uninstallable));
	 
	 if ($_POST['manifest'] == 'module') {
	 	 fwrite($fp, "\n");
		 fwrite($fp, createInstallDefs());	 	
	 }
	 fwrite($fp, "\n?>");
     fclose($fp);
    
  	
     if (!empty($unzip_dir)) {
     	echo "unliking the temp dir " . $unzip_dir;
     	rmdir_recursive($unzip_dir);
     }
     
  	if(!file_exists('zipatches/zips')){
    	sugar_mkdir('zipatches/zips');
  	}
  	chdir($dir);
  
  	
  	zip_dir('.', '../zips/zpatch'. $date. '.zip');
  	echo "<br/><br/><a href='zipatches/zips/zpatch" .$date. '.zip'."'>Download Patch</a>";
//  	header('Location: zipatches/zips/zpatch'. $date. '.zip');
}
?>
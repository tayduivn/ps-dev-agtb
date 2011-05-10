<?php
// FILE SUGARCRM flav=int ONLY 
if(!defined('sugarEntry'))define('sugarEntry', true);
require_once('include/utils/file_utils.php');

function createInstallDefs($name,$files, $tab=true,$admin=false, $images=false ){
if($images){
    $images = "'image_dir=>'<basepath>/$images',\n";    
}
require('include/modules.php');
if(empty($beanList[$name]))die('Module Does Not Exist');
$class = $beanList[$name];
$path = $beanFiles[$class];
$copyfiles = array();
foreach($files as $file){
    $copyfiles[] = array('from'=>'<basepath>/SugarPatch/'. $file, 'to'=>$file); 
}
$copyfilesstr = var_export($copyfiles, true);
if($tab){
    $tab = 'true';  
}else{
    $tab = 'false';
}
return <<<EOQ

\$installdefs = array (
  'id' => '$name',
  $images
 'copy' => $copyfilesstr,
  'beans'=> array(
                array('module'=> '$name',
                      'class'=> '$class',
                      'path'=> '$path',
                      'tab'=> $tab,
                      )
                ),
  );

EOQ;
}

function createManifest($name, $description, $type='patch', $is_uninstallable=true){
    
$date = date('Y-m-d H:i:s');
$time = time();
$is_uninstallable = ($is_uninstallable ? 'true' : 'false');
return  <<<EOQ

\$manifest = array (
  'acceptable_sugar_versions' => 
  array (
  ),
  'author' => 'SugarCRM, Inc.',
  'copy_files' => 
  array (
    'from_dir' => 'SugarPatch',
    'to_dir' => '',
    'force_copy' => 
    array (
    ),
  ),
  'description' => '$description',
  'icon' => '',
  'is_uninstallable' => $is_uninstallable,
  'name' => '$name',
  'published_date' => '$date',
  'type' => '$type',
  'version' => '$time',
  );

EOQ;
    
}
//<?php hack for eclipse text highlighting
if(!defined('sugarEntry'))define('sugarEntry', true);

if(!empty($_POST['filestopatch'])){

  require_once('include/utils/file_utils.php');
  require_once('include/dir_inc.php');

  $files = explode("\n",$_POST['filestopatch']);
  $date = date('Y_m_d_His');
  $is_uninstallable = (isset($_POST['is_uninstallable']) && $_POST['is_uninstallable'] == "on" ?
                       true :
                       false);
  
  if(!file_exists('zipatches'))
    mkdir('zipatches');
  
  $dir = 'zipatches/' . $date ;

  if(!file_exists($dir))
    mkdir($dir);

  if(!empty($_POST['manifest'])){
  
    $dirsecond = 'SugarPatch/portal/';
    
    if(!file_exists($dir. $dirsecond))
      mkdir_recursive($dir.$dirsecond, true);
      
  }else{
    $dirsecond = '';
  }

  foreach($files as $filepath){
  	if(empty($filepath))continue;
    $filepath = trim($filepath);
    $filepath  = str_replace('\\', '/', $filepath);
    mkdir_recursive($dir.'/'.$dirsecond. dirname($filepath), true);
	
    copy_recursive($filepath, $dir.'/'.$dirsecond. $filepath);
  }
  
  if(!empty($_POST['manifest'])){

    $patch_name = 'Sugar Internal Only';    
    $patch_description = 'For Use By Sugar CRM employees ONLY';

    if(!empty($_REQUEST['patch_name'])){
      $patch_name = $_REQUEST['patch_name'];
    }
    
    if(!empty($_REQUEST['patch_description'])){
      $patch_description = $_REQUEST['patch_description'];
    }
        
    $fp = fopen($dir . '/manifest.php', 'w');
    fwrite($fp, "<?php\n");
    $type = 'patch';
    if(!empty($_REQUEST['module']) && !empty($_REQUEST['module_name'])){
      $type = 'module';
    }
    
    fwrite($fp, createManifest($patch_name, $patch_description, $type, $is_uninstallable));
    
    if(!empty($_REQUEST['module']) && !empty($_REQUEST['module_name'])){
      fwrite($fp, createInstallDefs($_REQUEST['module_name'],
                                    $files,
                                    !empty($_REQUEST['module_tab']),
                                    !empty($_REQUEST['module_admin']))
            );
    }
    fwrite($fp, "\n?>");
    fclose($fp);
  }
  require_once('include/utils/zip_utils.php');
  if(!file_exists('zipatches/zips')){
    mkdir('zipatches/zips');
  }
  chdir($dir);
  
  zip_dir('.', '../zips/zpatch'. $date. '.zip');
  header('Location: zipatches/zips/zpatch'. $date. '.zip');
}

?>
<form method='post' name="theform">
<textarea name='filestopatch' cols='60' rows='10'>
modules/Cases/metadata/detailviewdefs.php
modules/Cases/metadata/editviewdefs.php
modules/Cases/metadata/listviewdefs.php
modules/Cases/metadata/studio.php
modules/Bugs/metadata/detailviewdefs.php
modules/Bugs/metadata/editviewdefs.php
modules/Bugs/metadata/listviewdefs.php
modules/Bugs/metadata/studio.php
modules/Leads/metadata/editviewdefs.php
modules/Leads/metadata/studio.php
</textarea>
<BR>
<font size = '2' color='Maroon'>Installable Patch:<input type ='checkbox' name='manifest' onclick='if(this.checked){document.getElementById("manifestspan").style.display = "inline"; document.theform.is_uninstallable.disabled = false;}else{document.getElementById("manifestspan").style.display = "none"; document.theform.is_uninstallable.disabled = true;}'> &nbsp;Uninstallable:<input type ='checkbox' name='is_uninstallable' disabled>&nbsp;<input type='submit' value='Create Patch'><BR>

<span id='manifestspan' style="display:none">
<HR>
Manifest Name:<input type='text' name='patch_name' value='Sugar Internal Only'>&nbsp;|&nbsp;Description:<input type='text' name='patch_description' size='30' value='For Use By Sugar CRM employees ONLY'>
<br>Installable Module:<input type ='checkbox' name='module' onclick='if(this.checked){document.getElementById("modulespan").style.display = "inline"}else{document.getElementById("modulespan").style.display = "none"}'> &nbsp;
<span id='modulespan' style="display:none">
Module Name:<input type='text' name='module_name' value=''>&nbsp;|&nbsp; Tab:<input type ='checkbox' name='module_tab' checked>&nbsp;|&nbsp; Admin Section:<input type ='checkbox' name='module_admin'>
</span>
</span>
</font>
<BR>
<br>
</form>
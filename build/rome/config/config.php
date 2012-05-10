<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$config = array();
$config['license']['search'] = array('contents of this file are subject to', 'SugarCRM" logo. If the display of the logo');
$config['excludeFileTypes'] = array('.png'=>1, '.gif'=> 1, '.jpg'=>1, '.swf'=>1, 'README'=>1, '.eot' => 1);
$config['excludeFiles'] = array('sugarportal/jscalendar/lang/calendar-hr.js'=>1);
$config['skipBuilds'] = array('spotactions'=>1, 'richtext'=>1, 'sugarmdle'=>1, 'following'=>1,'inlineedit'=>1, 'notifications'=>1,'sugarsurvey'=>1, 'int'=>1,'internal'=>1 );
$config['skipDirs'] = array('.AppleDouble'=>1,'Bitrock'=>1,'webpi'=>1, 'rome'=>1, 'scripts'=>1, 'aps'=>1, 'translations'=>1 );
$config['registry'] = array('reg'=>array(), 'lic'=>array(), 'flav'=>array(), 'dep'=>array());
$config['registry']['dep'] = array('os'=>1, 'od'=>1, 'een' => 1);
$config['registry']['reg'] = array('zh_cn'=>1);
$config['registry']['lic'] = array('sub'=>1, 'gpl'=>1);
$config['sugarVariables'] = array('@_SUGAR_VERSION'=>'','@_SUGAR_FLAV'=>'' );
$config['mergeDirs'] = array('translations'=>'sugarcrm');

$config['builds']['exp']['flav'] = array('com');
$config['builds']['exp']['lic'] = array('sub'=>'sub');

$config['builds']['pro']['flav'] = array('pro');
$config['builds']['pro']['lic'] = array('sub');

$config['builds']['corp']['flav'] = array('pro','corp');
$config['builds']['corp']['lic'] = array('sub');

$config['builds']['ent']['flav'] = array('pro','ent','corp');
$config['builds']['ent']['lic'] = array('sub');

$config['builds']['ult']['flav'] = array('pro','corp','ent','ult');
$config['builds']['ult']['lic'] = array('sub');

$config['builds']['dev']['flav'] = array('een','ent','pro','dev','ult','corp');
$config['builds']['dev']['lic'] = array('sub');

$config['builds']['dce']['flav'] = array('ent'=>1, 'pro'=>1);
$config['builds']['dce']['lic'] = array('sub');

$config['product']= array("com","dce","dev","eng","exp","pro");
$config['replace'] = array('$Id:','$Log:','$Header$', '$Id$');

//Controls whether or not to include the original line numbering (i.e. commented lines appear as newlines)
$config['retainCommentSpacing'] = false;

$d = dir('config/builds');
while($e = $d->read()){
	$path = 'config/builds/' . $e;
	if(is_file($path) && substr($e, 0, 6) == 'config'){
		include($path);
	}
}
foreach($config['skipBuilds'] as $flav=>$x){
	define($flav, $flav);
}
foreach($config['registry']['dep'] as $flav=>$x){
	define($flav, $flav);
}
foreach($config['registry']['lic'] as $flav=>$x){
	define($flav, $flav);
}
foreach($config['registry']['reg'] as $flav=>$x){
	define($flav, $flav);
}
foreach($config['builds'] as $flav=>$info){
		if(!defined($flav))define($flav, $flav);
       	if(empty($config['builds'][$flav]['dep']))$config['builds'][$flav]['dep'][] = 'os';

        //load the license for the build
        $config['license'][$flav] = file_get_contents('license/header.' . $flav . '.txt');
}

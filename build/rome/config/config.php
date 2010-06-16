<?php
$config = array();
$config['license']['search'] = 'contents of this file are subject to';
$config['excludeFileTypes'] = array('.png'=>1, '.gif'=> 1, '.jpg'=>1, '.swf'=>1, 'README'=>1);
$config['excludeFiles'] = array('sugarportal/jscalendar/lang/calendar-hr.js'=>1);
$config['skipBuilds'] = array('spotactions'=>1, 'richtext'=>1, 'sugarmdle'=>1, 'following'=>1,'inlineedit'=>1, 'notifications'=>1,'sugarsurvey'=>1, 'int'=>1,'internal'=>1 );
$config['skipDirs'] = array('Bitrock'=>1,'webpi'=>1, 'rome'=>1, 'scripts'=>1, 'aps'=>1 );
$config['registry'] = array('reg'=>array(), 'lic'=>array(), 'flav'=>array(), 'dep'=>array());
$config['registry']['dep'] = array('os'=>1, 'od'=>1, 'een' => 1);
$config['registry']['reg'] = array('zh_cn'=>1);
$config['registry']['lic'] = array('sub'=>1, 'gpl'=>1);
$config['sugarVariables'] = array('@_SUGAR_VERSION'=>'','@_SUGAR_FLAV'=>'' );

$config['builds']['exp']['flav'] = array('com');
$config['builds']['exp']['lic'] = array('sub'=>'sub');

$config['builds']['pro']['flav'] = array('pro');
$config['builds']['pro']['lic'] = array('sub');

$config['builds']['ent']['flav'] = array('pro','ent');
$config['builds']['ent']['lic'] = array('sub');


$config['builds']['dev']['flav'] = array('een', 'ent','pro', 'com');
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

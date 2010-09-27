<?php

$config = array();
$config['languages']['gitPath'] = '../../translations'; 
$config['build_dir'] = 'builds';
$config['regions'] = array();
$config['deployments'] = array("od");
$config['skipFlavs'] = array('od_dce'=>1, 'od_ent'=>1,'od_com'=>1, 'od_dev'=>1, 'od_een'=>1, 'od_pro'=>1, 'od_sales'=>1, 'exp'=>1);
$config['exclusive'] = false;
//$config['base_dir'] = '../../sugarcrm';
$config['base_dir'] = '../../';
$config['clean'] = true;

$config['svn_path'] = 'http://svn1.sjc.sugarcrm.pvt/sugarcrm/branches/Pineapple';

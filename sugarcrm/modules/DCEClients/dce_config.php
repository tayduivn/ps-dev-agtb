<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
global $dce_config;

$dce_config = array(
    'dce_dbServer'      => 'localhost', //used to connect to dce db
    'dce_dbUser'        => 'sugarUser', //used to connect to dce db
    'dce_dbPass'        => 'sugarPass', //used to connect to dce db
    'dce_dbName'        => 'dce',   //used to connect to dce db

    'client_dbServer'      => 'localhost', //db host used to connect to create Instance DB's
    'client_dbUser'        => 'sugarUser', //db user used to connect to create Instance DB's
    'client_dbPass'        => 'sugarPass', //db pass used to connect to create Instance DB'sused to connect to dce db
    'client_mysql_path'     => '', //physical path to mysql, if it is not already in user path
    
    'client_templatePath'   => '/var/www/templates',  //path to where unzipped templates are kept
    'client_Templ_URL'      => 'http://localhost/templates', //web url to converted templates
    'client_dir_path'       => '/var/www/sugarclient',   //path to directory holding Client Code contents.  Used by template engine 
    'client_archivePath'    => '/var/www/sugarclient/archives', //path to where archives are to be stored
    'client_instancePath'   => '/var/www/web', //path to where instances are to be stored (in web root) 
    'client_baseURL'        => 'http://localhost/web', //base url to be used as site_url and to create links
    'client_cluster_user'   => 'apache',     //shell user on cluster to use for ownership 
    'client_cluster_group'  => 'apache',   //shell group on cluster to use for ownership
    'client_cluster_id'     => '4470d368-c708-c7c8-1682-47d57b1661ca',   //id of cluster on DCE side.  This is needed to identify cluster during job request
    'job_per_call'      => '1',     //number of dce jobs to process per cron call. 
    'upgradeBusyPage'   => '/var/www/sugarclient/busy.html', //during upgrade, this is the file that is used as busy page
    'active_clients'      => array('MyServerName1','MyServerName2', 'localhost') //host name of dce clients that are active for processing jobs. If ip is not in this array, jobs will not be processed.
                               
);

?>

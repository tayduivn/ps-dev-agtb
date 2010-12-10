<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=dce ONLY

global $sugar_config;
global $timedate;

///////////////  Start with email templates for DCE

//create email templates and store values in sugar_config
$EmailTemp = new EmailTemplate();

//succesful create insert template
$subj ='Your Sugar Instance is ready!';
$desc = 'This is the template for succesful instance creations';
$body = "<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>Congratulations, your Sugar Instance is ready.</p>  <p>SugarCRM wants you to have the most successful experience and will assist you at any time.</p>   <h2>Get Started with Sugar!</h2>  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">         <tbody><tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your unique URL:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">#inst.url#<br /></td></tr>          <tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your Admin User Name:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">admin</td></tr>         <tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your Admin Password:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">#inst.pass#</td></tr>         <tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>This eval account will expire at:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">#inst.expire#</td></tr>  </tbody></table><p>Welcome to the Sugar community.</p>  <p>&nbsp;</p>  <p>Sincerely,</p>  <p><strong>SugarCRM</strong></p>              </td>         </tr> </tbody></table> </div>";
$txt_body =
"
Congratulations, your Sugar Instance is ready. \r\n
SugarCRM wants you to have the most successful experience and will assist you at any time.\r\n
Please note your Instance information below:\r\n
\r\n
Your unique URL: #inst.url#\r\n
Your Admin User Name: admin \r\n
Your Admin Password: #inst.pass#\r\n
This eval account will expire on: #inst.expire#\r\n
\r\n
SugarCRM can help you make the most out of this evaluation with the following services:\r\n
    - Access to a 30-day evaluation of Sugar Plug-ins for Microsoft Outlook & Microsoft Word. This allows your users to synchronize contacts, emails calendars and Word based communications with SugarCRM. You will receive download instructions in the next email.\r\n
    - Sugar University - an online training resource to help you learn how to efficiently use, administer and customize your evaluation. Additional fees may apply.\r\n
    - Data migration guidance and services so that you can do a comparison with your existing system to Sugar Professional On-Demand. Additional fees may apply.\r\n
    - Portal based evaluation technical support.\r\n
    - Transition from your evaluation account to a production environment either On-Demand or On-Site.  Your SugarCRM team will make sure your company stays in control of your CRM solution.\r\n
Please contact us at sales@sugarcrm.com for additional information about these offerings.\r\n
You will be contacted by a SugarCRM representative to make sure your experience exceeds your expectations. Within 1 business day, you will receive a comprehensive email sharing contact information, training, documentation and support for your evaluation account.\r\n
\r\n
Welcome to the Sugar community.\r\n";
$name = 'Succesful Instance Creation Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['create'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'create_tmpl')");

//succesful create eval insert template
$EmailTemp = new EmailTemplate();
$subj ='Your Evaluation Sugar Instance is ready!';
$desc = 'This is the template for succesful eval instance creations';
$body = "<div> <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;center\&quot;\" bgcolor=\"#00fff0\">         <tbody>  <tr>             <td colspan=\"2\" style=\"padding-left: 55px; font-size: 12px; padding-bottom: 30px; color: #444444; padding-top: 30px; font-family: Arial\">             <p>Signing up for this 30 day free trial is the first step for getting your company on track to taking control of customer relationships, group collaboration, sales forecasts, customer support, and marketing management. Congratulations.</p>  <p>SugarCRM wants you to have the most successful experience during your evaluation and will assist you at any time.</p>   <h2>Get Started with Sugar Professional On-Demand</h2>  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">         <tbody><tr><td valign=\"\&quot;top\&quot;\"><strong>Your unique URL:</strong></td>                 <td valign=\"\&quot;top\&quot;\"><a href=\"%5C%22#inst.url#%5C%22\">#inst.url#</a></td></tr>          <tr><td valign=\"\&quot;top\&quot;\"><strong>Your Admin User Name:</strong></td>                 <td valign=\"\&quot;top\&quot;\">admin</td></tr>         <tr><td valign=\"\&quot;top\&quot;\"><strong>Your Admin Password:</strong></td>                 <td valign=\"\&quot;top\&quot;\">#inst.pass#</td></tr>         <tr><td valign=\"\&quot;top\&quot;\"><strong>This eval account will expire at:</strong></td>                 <td valign=\"\&quot;top\&quot;\">#inst.expire#</td></tr>  </tbody></table>                                                         <p>SugarCRM can help you make the most out of this evaluation with the following services:</p>  <ul style=\"margin: 0pt\"> <li>Access to a 30-day evaluation of <strong>Sugar Plug-ins for Microsoft Outlook &amp; Microsoft Word</strong>. This allows your users to synchronize contacts, emails calendars and Word based communications with SugarCRM. You will receive download instructions in the next email. </li><li><strong>Sugar University</strong> - an online training resource to help you learn how to efficiently use, administer and customize your evaluation. Additional fees may apply. </li><li><strong>Data migration</strong> guidance and services so that you can do a comparison with your existing system to Sugar Professional On-Demand. Additional fees may apply.  </li><li>Portal based <strong>evaluation technical support.</strong> </li><li><strong>Transition from your evaluation account</strong> to a production environment either On-Demand or On-Site.  Your SugarCRM team will make sure your company stays in control of your CRM solution.  </li></ul>   <p>You will be contacted by a SugarCRM representative to make sure your experience exceeds your expectations. Within 1 business day, you will receive a comprehensive email sharing contact information, training, documentation and support for your evaluation account. Welcome to the Sugar community.</p>  <p>&nbsp;</p>  <p>Sincerely,</p>  <p><strong>SugarCRM</strong></p>              </td>         </tr> </tbody></table> </div> ";
$txt_body =
"
Signing up for this 30 day free trial is the first step for getting your company on track to taking control of customer relationships, group collaboration, sales forecasts, customer support, and marketing management. Congratulations.

SugarCRM wants you to have the most successful experience during your evaluation and will assist you at any time.
Get Started with Sugar Professional On-Demand.

Your unique URL: #inst.url#
Your Admin User Name: admin
Your Admin Password: #inst.pass#
This eval account will expire on: #inst.expire#

SugarCRM can help you make the most out of this evaluation with the following services:
    - Access to a 30-day evaluation of Sugar Plug-ins for Microsoft Outlook & Microsoft Word. This allows your users to synchronize contacts, emails calendars and Word based communications with SugarCRM. You will receive download instructions in the next email.
    - Sugar University - an online training resource to help you learn how to efficiently use, administer and customize your evaluation. Additional fees may apply.
    - Data migration guidance and services so that you can do a comparison with your existing system to Sugar Professional On-Demand. Additional fees may apply.
    - Portal based evaluation technical support.
    - Transition from your evaluation account to a production environment either On-Demand or On-Site.  Your SugarCRM team will make sure your company stays in control of your CRM solution.

Please contact us at sales@sugarcrm.com for additional information about these offerings.
You will be contacted by a SugarCRM representative to make sure your experience exceeds your expectations. Within 1 business day, you will receive a comprehensive email sharing contact information, training, documentation and support for your evaluation account.

Welcome to the Sugar community.";
$name = 'Succesful Eval Instance Creation Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['eval'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'eval_tmpl')");



//Archive template
$EmailTemp = new EmailTemplate();
$subj ='Your Instance has been archived';
$desc = 'This is the template for archive alerts';
$body = "<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>This is to let you know that instance #inst.name# has been archived. </p><p>regards,</p><p><strong>SugarCRM</strong></p>";
$txt_body =
'
This is to let you know that instance #inst.name# has been archived.

regards,
SugarCRM';
$name = 'Succesful Instance Archive Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['archive'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'archive_tmpl')");

//toggle template
$EmailTemp = new EmailTemplate();
$subj ='Your Support user has been created!';
$desc = 'This is the template for creation of toggle user';
$body = "<p>Your Toggle User has been created.&nbsp; It will be deleted in approximately #cfg.support_time_limit# hours.</p><p>You may log in with the following information:&nbsp;</p><p>User Login: #usr.name#</p><p>User Pass: #usr.pass# <br />Site Url: #inst.url#  <br /><br /> </p> <br /> regards,<br />SugarCRM";
$txt_body =
'
Your Toggle User has been created.  It will be deleted in approximately #cfg.support_time_limit# hours.
You may log in with the following information:

User Login: #usr.name#
User Pass: #usr.pass#
Site Url: #inst.url#

regards,
SugarCRM';
$name = 'Support User Request Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['toggle'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'toggle_tmpl')");

//succesful upgrade for testing template
$EmailTemp = new EmailTemplate();
$subj ='Upgraded Test Sugar Instance is ready!';
$desc = 'This is the template for succesful creation of test upgraded instances';
$body = "<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>Sugar Instance #parent.name# was cloned and upgraded succesfully.</p>   <p>Please note your Upgraded Test Instance information below</p>  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">         <tbody><tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your unique URL:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">#inst.url#<br /></td></tr>          <tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your Admin User Name:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">admin</td></tr>         <tr><td valign=\"\&quot;\&quot;top\&quot;\&quot;\"><strong>Your Admin Password:</strong></td>                 <td valign=\"\&quot;\&quot;top\&quot;\&quot;\">#inst.pass#</td></tr>         </tbody></table><p>Welcome to the Sugar community.</p>  <p>&nbsp;</p>  </td>         </tr> </tbody></table> </div>";
$txt_body =
"
Sugar Instance #parent.name# was cloned and upgraded succesfully.
Please note your Upgraded Test Instance information below:

Your unique URL: #inst.url#
Your Admin User Name: admin
Your Admin Password: #inst.pass#
";
$name = 'Upgraded Instance Available Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['upgrade_test'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'upgrade_test_tmpl')");

//succesful upgrade for testing template
$EmailTemp = new EmailTemplate();
$subj ='Upgraded Sugar Instance is ready!';
$desc = 'This is the template for succesful creation of live upgraded instances';
$body = "<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>Sugar Instance #inst.name# was upgraded succesfully.</p>   </td>         </tr> </tbody></table> </div>";
$txt_body =
"
Sugar Instance #inst.name# was upgraded succesfully!
";
$name = 'Upgraded Live Instance Available Email Template';

$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['upgrade_live'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'upgrade_live_tmpl')");

//succesful upgrade for testing template
$EmailTemp = new EmailTemplate();
$subj ='ALERT! DCE actions have failed!';
$desc = 'This is the template for alerting it staff of failed actions';
$body = "<div><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"550\" align=\"\&quot;\&quot;center\&quot;\&quot;\"><tbody><tr><td colspan=\"2\"><p>This email is to alert you that actions have failed for instance:  #inst.name#.  </p>The logs are included below:   </td>         </tr><tr><td colspan=\"2\"></td>         </tr> </tbody></table> </div>";
$txt_body =
"
This email is to alert you that actions have failed for instance:  #inst.name#.  The action status has been changed to suspended.
";

$name = 'DCE Action Error Template';
$EmailTemp->name = $name;
$EmailTemp->description = $desc;
$EmailTemp->subject = $subj;
$EmailTemp->body = $txt_body;
$EmailTemp->body_html = $body;
$EmailTemp->deleted = 0;
$EmailTemp->team_id = 1;
$EmailTemp->published = 'off';
$EmailTemp->text_only = 0;
$id =$EmailTemp->save();

//$sugar_config['dce_EmailTemplate']['failed'] = $id;
$result = $EmailTemp->db->query("INSERT INTO config (value, category, name) VALUES ('$id','dce', 'failed_tmpl')");


//rebuild sugarconfig with new values
rebuildConfigFile($sugar_config, $sugar_config['sugar_version']);



///////////////  End email templates for DCE


    ////Begin Seed Data
    $clusters = array();
    $templates = array();
    $instances = array();
    $actions = array();

    /////////////hardcode the first set, so we can control the guid

    $cluster = new DCECluster();
    $cluster->name = 'Alpha Cluster';
    $cluster->id = '4470d368-c708-c7c8-1682-47d57b1661ca';
    $cluster->team_id = '1';
    $cluster->modified_user_id = '1';
    $cluster->created_by = '1';
    $cluster->assigned_user_id = '1';
    $cluster->server_status = 'active';
    $cluster->url = 'http://localhost';
    $cluster->new_with_id = true;
    $cluster->save();
    $clusters[] = $cluster->id;


    $temp = new DCETemplate();
    $temp->name = 'SugarEnt-Full-5.1.0-Honey';
    $temp->id = 'bad0d368-c708-c7c8-1682-47d57b16mnky';
    $temp->team_id = '1';
    $temp->modified_user_id = '1';
    $temp->assigned_user_id = '1';
    $temp->created_by = '1';
    $temp->convert_status = 'yes';
    $temp->copy_template = 0;
    $temp->server_status = 'active';
    $temp->sugar_version= '5.1.0';
    $temp->sugar_edition= 'ENT';
    $temp->template_name= 'SugarEnt-Full-5.1.0-Honey';
    $temp->zip_name= 'SugarEnt-5.1.0-Honey.zip';
    $temp->new_with_id = true;
    $temp->save();
    $templates[] = $temp->id;

    //create dummy/copy template for existing instances
    $cpy_temp = new DCETemplate();
    $cpy_temp->name = 'Sugar-CopyTemplate';
    $cpy_temp->id = 'ypt0d368-c708-c7c8-1682-47d57b16copy';
    $cpy_temp->team_id = '1';
    $cpy_temp->modified_user_id = '1';
    $cpy_temp->assigned_user_id = '1';
    $cpy_temp->created_by = '1';
    $cpy_temp->convert_status = 'yes';
    $cpy_temp->copy_template = 1;
    $cpy_temp->server_status = 'active';
    $cpy_temp->sugar_version= '0.0.0';
    $cpy_temp->sugar_edition= 'CPY';
    $cpy_temp->template_name= 'copy';
    $cpy_temp->zip_name= 'none.zip';
    $cpy_temp->new_with_id = true;
    $cpy_temp->save();


    //create DCE account

    $acc = new Account();
    $acc->name = 'DCE Puppet Master';
    $acc->id = 'mastd368-c7er-cof8-16pu-47d57b16pets';
    $acc->team_id = '1';
    $acc->modified_user_id = '1';
    $acc->assigned_user_id = '1';
    $acc->new_with_id = true;
    $acc->save();
    $accounts[] = $acc->id;

    ////////////End of hardcoding


    //create templates
    $templates = createTemplates();
    $templates[] = $temp->id;

    //retrieve accounts
    $accounts = retrieveAccounts();

    //define number of clusters to create
    $num_of_clusters = 3;
    //create cluster
    $clusters = createClusters($num_of_clusters);
    $clusters[] = $cluster->id;

    //define number of instances to create
    $num_of_instances =20;
    //create instances
    $instances = createInstances($num_of_instances,$clusters,$templates,$accounts);

    //define number of actions TOTAL (Distributed randomly across instances)
    $num_of_actions = 200;
    //create related actions
    $actions = createSeedActions($num_of_actions,$clusters,$templates,$instances);

    //define number of days of report data to create per instance
    $num_of_days = 5;
    //create dce report seed data
    createDCEReports($num_of_days, $instances);








    function createClusters($num_of_clusters){
        $clusterNamesArr = array('Beta', 'Gamma', 'DELTA', 'Epsilon', 'Zeta', 'Eta', 'Theta', 'Iota', 'Kappa', 'Lambda', 'Mu', 'Nu', 'Xi', 'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'UPSILON', 'Phi', 'Chi', 'Psi', 'Omega',);
        $clusterCount = count($clusterNamesArr)-1;


        $count = 0;
        while($count<$num_of_clusters){
            $count = $count +1;
            $cluster = new DCECluster();
            $cluster->name = $clusterNamesArr[mt_rand(0,$clusterCount)].' Cluster';
            $cluster->team_id = '1';
            $cluster->modified_user_id = '1';
            $cluster->created_by = '1';
            $cluster->assigned_user_id = '1';
            $cluster->server_status = 'active';
            $cluster->url = 'http://localhost';
            $clusters[] = $cluster->save();
        }

        return $clusters;
    }


    function createTemplates(){
       $templates = array();
       $templateVersionsArr = array(
            //array('name'=>'SugarEnt-Full-5.1.0', 'flavor'=>'ENT', 'version' =>'5.1.0', 'zip'=>'SugarEnt-5.1.0.zip'),
            array('name'=>'SugarCE-Full-5.1.0RC-Honey', 'flavor'=>'CE', 'version' =>'5.1.0RC', 'zip'=>'SugarCE-5.1.0RC-Honey.zip', 'copy_template'=>0),
            array('name'=>'SugarPro-Full-5.1.0RC-Honey', 'flavor'=>'PRO', 'version' =>'5.1.0RC', 'zip'=>'SugarPro-5.1.0RC-Honey.zip', 'copy_template'=>0),
        );


        foreach($templateVersionsArr as $templateInfo){
            $temp = new DCETemplate();
            $temp->name = $templateInfo['name'];
            $temp->team_id = '1';
            $temp->modified_user_id = '1';
            $temp->assigned_user_id = '1';
            $temp->created_by = '1';
            $temp->server_status = 'active';
            $temp->convert_status = 'yes';
            $temp->sugar_version= $templateInfo['version'];
            $temp->sugar_edition= $templateInfo['flavor'];
            $temp->template_name= $templateInfo['name'];
            $temp->zip_name= $templateInfo['zip'];
            $temp->copy_template =$templateInfo['copy_template'];

            $templates[] = $temp->save();
        }
        return $templates;
    }

    function createInstances($num_of_instances,$clusters,$templates,$accounts){
        global $timedate;
        $instances = array();
        $instanceNamesArr = array(
        //BEGIN SUGARCRM flav=int ONLY
            'Zeus', 'Poseidon', 'Hestia', 'Hermes', 'Hera', 'Hades', 'Athena', 'Artemis', 'Ares', 'Apollo', 'Aphrodite', 'Baldr', 'Frey', 'Frigg', 'Loki', 'Odin', 'Thor', 'Brahma', 'Vishnu', 'Shiva', 'Krishna', 'Rama', 'Ehecatl', 'Mextli', 'Omacatl', 'LeiGong', 'XuanXuan', 'Nuwa', 'Fuxi', 'Shangdi',
        //END SUGARCRM flav=int ONLY
            'Dept1', 'Dept2', 'Dept3', 'Dept4', 'Dept5', 'Dept6', 'Dept7', 'Dept8', 'Dept9', 'Dept10', 'Dept11',
            'Region1', 'Region2', 'Region3', 'Region4', 'Region5', 'Region6', 'Region7', 'Region8', 'Region9', 'Region10', 'Region11',
            'Zone1', 'Zone2', 'Zone3', 'Zone4', 'Zone5', 'Zone6', 'Zone7', 'Zone8', 'Zone9', 'Zone10', 'Zone11',
        );
        $users = retrieveUsers();

        $instCount = count($instanceNamesArr)-1;
        $clstCount = count($clusters)-1;
        $templCount = count($templates)-1;
        $accCount = count($accounts)-1;
        $usrsCount = count($users)-1;


        $count = 0;
        while ($count<$num_of_instances){
            $count = $count+1;
            $instance_type = returnInstanceType();
            $inst = new DCEInstance();
            $inst->name = $instanceNamesArr[mt_rand(0,$instCount)].'_inst'.$count;
            $inst->team_id = '1';
            $inst->modified_user_id = '1';
            $inst->assigned_user_id = '1';
            $inst->created_by = '1';
            $inst->status = returnInstanceStatus();
            $inst->account_id = $accounts[mt_rand(0,$accCount)];
            $inst->license_key = create_guid();
            $inst->license_duration = returnLicenseDuration($instance_type);
            $inst->license_start_date = $timedate->nowDbDate();
            $inst->license_expire_date = $inst->returnExpirationDate($inst->license_start_date,$inst->license_duration);
            $inst->licensed_users = mt_rand(1,15);
            $inst->type =$instance_type;
            $inst->dcetemplate_id= $templates[mt_rand(0,$templCount)];//gettemplate
            $inst->dcecluster_id= $clusters[mt_rand(0,$clstCount)];
            $inst->url = "http://localhost/".$inst->name;
            $inst->get_key_user_id = $users[mt_rand(0,$usrsCount)];
            $inst->license_key_status = mt_rand(0,1);
            $inst->from_copy_template = '0';
            if(mt_rand(0,1)){
                $inst->update_key_user_id = $users[mt_rand(0,$usrsCount)];
            }
            $instances[$inst->name] = $inst->save();
            relateAccount($inst);
        }
        return $instances;
    }


    function createSeedActions($num_of_actions, $clusters, $templates, $instances){
        global $timedate;
        $actionTypesArr = array('create', 'convert', 'clone', 'archive', 'recover', 'toggle_on', 'toggle_off', 'upgrade_live', 'upgrade_test');
        $actionStatusArr = array('queued', 'started', 'pending', 'suspended', 'completed', 'done', 'failed',);

        $typeCount = count($actionTypesArr)-1;
        $statusCount = count($actionStatusArr)-1;
        $instCount = count($instances)-1;
        $clstCount = count($clusters)-1;
        $templCount = count($templates)-1;

        //convert array into number key
        $instancesByNum = array();
        $count = 0;
        foreach ($instances as $k =>$v){
            $instancesByNum[$count]['name'] = $k;
            $instancesByNum[$count]['id'] = $v;
            $count = $count +1;
        }

        //for each instance
        $count = 0;
        while ($count < $num_of_actions){
            $type = $actionTypesArr[mt_rand(0,$typeCount)];
            $use_inst = $instancesByNum[mt_rand(0,$instCount)];
            //create dce action

            $action = new DCEAction();
            $action->name = $use_inst['name'].' '.$type.' action';
            $action->instance_id = $use_inst['id'];
            $action->cluster_id = $clusters[mt_rand(0,$clstCount)];
            $action->template_id = $templates[mt_rand(0,$templCount)];
            $action->type = $type;
            $action->status = $actionStatusArr[mt_rand(0,$statusCount)];
            $action->start_date = $timedate->now();
            $action->priority = -1;
            $action->action_parms .= ', previous_status:'.returnInstanceStatus();
            $action->save();
            $count = $count+1;
        }

    }


    function createDCEReports($numdays = 3, $InstanceArr){


        //for each instance
        foreach($InstanceArr as $name =>$id){
            $count = 0;
            //create n days of data for this instance
            while( $count < $numdays){
                $count = $count+1;
                $time = returnTimeRanges($count);
                    //foreach date range
                    foreach($time as $range => $date){
                        $rprt = new DCEReport();
                        $rprt->name = $name.'_'.$range;
                        $rprt->deleted = 0;
                        $rprt->team_id = '1';
                        $rprt->num_of_logins  = mt_rand(1,150);
                        $rprt->num_of_users = mt_rand(1,15);
                        $rprt->max_num_sessions = mt_rand(0, mt_rand(2,50));
                        $rprt->num_of_requests = mt_rand(0, mt_rand(13,10000));
                        $rprt->memory = mt_rand(0, mt_rand(5000,100000));
                        $rprt->num_of_files = mt_rand(0, mt_rand(100,20000));
                        $rprt->num_of_queries = mt_rand(0, mt_rand(100,30000));
                        $rprt->last_login_time = lastLogin($date);
                        $rprt->slow_logged_queries = '';
                        $rprt->instance_name = $name;
                        $rprt->instance_id= $id;
                        $rprt->time_start = $date['start'];
                        $rprt->time_end = $date['end'];
                        $rprt->save();
                    }
            }

        }

    }

    //this function randomly returns 1 of 3 options for the last log indate
    function lastLogin($date){
        $rand = mt_rand(0,2);
           if($rand == 0){
           		global $timedate;
           		return $timedate->to_display_date_time('0000-00-00 00:00:00');
           }else if($rand == 1){
                return $date['end'];
           }else{
                $date['start'];
           }


    }


    function retrieveAccounts(){
        $accounts = array();

        $acc = new Account();
        $accQry = "select id from accounts where deleted = 0";
        $res = $acc->db->query($accQry);

        while(($ac = $acc->db->fetchByAssoc($res))!= null){
            $accounts[] = $ac['id'];
        }

        return $accounts;


    }


    function retrieveUsers(){

        $acc = new User();
        $accQry = "select id from users where deleted = 0";
        $res = $acc->db->query($accQry);

        $users = array();
        while(($ac = $acc->db->fetchByAssoc($res))!= null){
            $users[] = $ac['id'];
        }
        return $users;


    }

    //this function randomly returns 1 of 3 options for the last log indate
    function returnLicenseDuration($type){
        $duration = array();
        if ( $type == 'evaluation'){
            $duration = array( '15', '30', '45', '60', '90');
        }else{
            $duration = array('90', '180', '365', '730', '1095');
        }
        $durCount = count($duration)-1;

        return $duration[mt_rand(0,$durCount)];

    }


    function returnInstanceStatus(){
        $Status = array('new', 'live', 'in_progress', 'archived',);
        $statusCount = count($Status)-1;
        return $Status[mt_rand(0,$statusCount)];

    }

    function returnInstanceType(){
        $Types = array('evaluation', 'production');
        $typeCount = count($Types)-1;
        return $Types[mt_rand(0,$typeCount)];

    }


    //this function sets the time ranges for the dce report seed data
    function returnTimeRanges($datesToRemove = 1){
        global $timedate;
        $yesterday = $timedate->asDbDate($timedate->getNow()->get("yesterday"));

        //create the 24 hour ranges
        $hour['range1']['start'] = $timedate->to_display_date_time($yesterday. ' 0:00:00');
        $hour['range1']['end']   = $timedate->to_display_date_time($yesterday. ' 0:59:59');
        $hour['range2']['start'] = $timedate->to_display_date_time($yesterday. ' 1:00:00');
        $hour['range2']['end']   = $timedate->to_display_date_time($yesterday. ' 1:59:59');
        $hour['range3']['start'] = $timedate->to_display_date_time($yesterday. ' 2:00:00');
        $hour['range3']['end']   = $timedate->to_display_date_time($yesterday. ' 2:59:59');
        $hour['range4']['start'] = $timedate->to_display_date_time($yesterday. ' 3:00:00');
        $hour['range4']['end']   = $timedate->to_display_date_time($yesterday. ' 3:59:59');
        $hour['range5']['start'] = $timedate->to_display_date_time($yesterday. ' 4:00:00');
        $hour['range5']['end']   = $timedate->to_display_date_time($yesterday. ' 4:59:59');
        $hour['range6']['start'] = $timedate->to_display_date_time($yesterday. ' 5:00:00');
        $hour['range6']['end']   = $timedate->to_display_date_time($yesterday. ' 5:59:59');
        $hour['range7']['start'] = $timedate->to_display_date_time($yesterday. ' 6:00:00');
        $hour['range7']['end']   = $timedate->to_display_date_time($yesterday. ' 6:59:59');
        $hour['range8']['start'] = $timedate->to_display_date_time($yesterday. ' 7:00:00');
        $hour['range8']['end']   = $timedate->to_display_date_time($yesterday. ' 7:59:59');
        $hour['range9']['start'] = $timedate->to_display_date_time($yesterday. ' 8:00:00');
        $hour['range9']['end']   = $timedate->to_display_date_time($yesterday. ' 8:59:59');
        $hour['range10']['start'] = $timedate->to_display_date_time($yesterday.' 9:00:00');
        $hour['range10']['end']   = $timedate->to_display_date_time($yesterday.' 9:59:59');
        $hour['range11']['start'] = $timedate->to_display_date_time($yesterday.' 10:00:00');
        $hour['range11']['end']   = $timedate->to_display_date_time($yesterday.' 10:59:59');
        $hour['range12']['start'] = $timedate->to_display_date_time($yesterday.' 11:00:00');
        $hour['range12']['end']   = $timedate->to_display_date_time($yesterday.' 11:59:59');
        $hour['range13']['start'] = $timedate->to_display_date_time($yesterday.' 12:00:00');
        $hour['range13']['end']   = $timedate->to_display_date_time($yesterday.' 12:59:59');
        $hour['range14']['start'] = $timedate->to_display_date_time($yesterday.' 13:00:00');
        $hour['range14']['end']   = $timedate->to_display_date_time($yesterday.' 13:59:59');
        $hour['range15']['start'] = $timedate->to_display_date_time($yesterday.' 14:00:00');
        $hour['range15']['end']   = $timedate->to_display_date_time($yesterday.' 14:59:59');
        $hour['range16']['start'] = $timedate->to_display_date_time($yesterday.' 15:00:00');
        $hour['range16']['end']   = $timedate->to_display_date_time($yesterday.' 15:59:59');
        $hour['range17']['start'] = $timedate->to_display_date_time($yesterday.' 16:00:00');
        $hour['range17']['end']   = $timedate->to_display_date_time($yesterday.' 16:59:59');
        $hour['range18']['start'] = $timedate->to_display_date_time($yesterday.' 17:00:00');
        $hour['range18']['end']   = $timedate->to_display_date_time($yesterday.' 17:59:59');
        $hour['range19']['start'] = $timedate->to_display_date_time($yesterday.' 18:00:00');
        $hour['range19']['end']   = $timedate->to_display_date_time($yesterday.' 18:59:59');
        $hour['range20']['start'] = $timedate->to_display_date_time($yesterday.' 19:00:00');
        $hour['range20']['end']   = $timedate->to_display_date_time($yesterday.' 19:59:59');
        $hour['range21']['start'] = $timedate->to_display_date_time($yesterday.' 20:00:00');
        $hour['range21']['end']   = $timedate->to_display_date_time($yesterday.' 20:59:59');
        $hour['range22']['start'] = $timedate->to_display_date_time($yesterday.' 21:00:00');
        $hour['range22']['end']   = $timedate->to_display_date_time($yesterday.' 21:59:59');
        $hour['range23']['start'] = $timedate->to_display_date_time($yesterday.' 22:00:00');
        $hour['range23']['end']   = $timedate->to_display_date_time($yesterday.' 22:59:59');
        $hour['range24']['start'] = $timedate->to_display_date_time($yesterday.' 23:00:00');
        $hour['range24']['end']   = $timedate->to_display_date_time($yesterday.' 23:59:59');
        return $hour;
    }

    function relateAccount($inst){
        global $timedate;
        $roles = array('Primary Decision Maker', 'Technical Decision Maker');


        $acc = new Account();
        $acc->retrieve($inst->account_id);
        $acc->load_relationship('contacts');
        $relContacts = $acc->contacts->get();

        if(empty($relContacts)){ return;}
        $insrtQRY = "";
        //create insert query
        if($inst->db->dbType == 'oci8'){
            $insrtQRY = "INSERT INTO dceinstances_contacts (id , contact_id , instance_id , contact_role , date_modified , deleted )
            VALUES ('".create_guid()."', '".$relContacts[0]."', '".$inst->id."',
                    '".$roles[mt_rand(0,1)]."',  to_date('".$timedate->nowDb()."', 'YYYY-MM-DD HH24:MI:SS'), '0')";

        }else{
            $insrtQRY = "INSERT INTO dceinstances_contacts (id , contact_id , instance_id , contact_role , date_modified , deleted )
            VALUES ('".create_guid()."', '".$relContacts[0]."', '".$inst->id."',
                    '".$roles[mt_rand(0,1)]."', '".$timedate->nowDb()."', '0')";
        }
             //execute query
             $inst->db->query($insrtQRY);

    }





?>

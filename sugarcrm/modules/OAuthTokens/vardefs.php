<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$dictionary['OAuthToken'] = array('table' => 'oauth_tokens',
	'comment' => 'OAuth tokens',
	'audited'=>false,
	'fields' => array (
	  'id' =>
	  array (
	    'name' => 'id',
	    'vname' => 'LBL_ID',
	    'type' => 'id',
	    'required'=>true,
	    'reportable'=>true,
	    'comment' => 'Unique identifier'
	  ),
      'secret' =>
      array (
            'name' => 'secret',
            'type' => 'varchar',
            'len' => 32,
            'required' => true,
            'comment' => 'Secret key',
      ),
      'tstate' =>
      array (
            'name' => 'tstate',
            'type' => 'int',
            'required' => true,
            'comment' => 'Token state',
      ),
      'consumer' =>
      array (
            'name' => 'consumer',
            'type' => 'id',
            'required' => true,
            'comment' => 'Token related to the consumer',
      ),
      'token_ts' =>
      array (
            'name' => 'token_ts',
            'type' => 'long',
            'required' => true,
            'comment' => 'Token timestamp',
      ),
      'verify' =>
      array (
            'name' => 'verify',
            'type' => 'varchar',
            'len' => 32,
            'comment' => 'Token verification info',
      ),
//      'authdata' =>
//      array (
//            'name' => 'verify',
//            'type' => 'text',
//            'comment' => 'Token auth data',
//      ),
	  'deleted' =>
	  array (
	    'name' => 'deleted',
	    'vname' => 'LBL_DELETED',
	    'type' => 'bool',
	    'default' => '0',
	    'reportable'=>false,
	    'comment' => 'Record deletion indicator'
	  ),
      'consumer_link' =>
      array (
        'name' => 'consumer_link',
        'type' => 'link',
        'relationship' => 'consumer',
        'vname' => 'LBL_CONSUMER',
        'link_type' => 'one',
        'module'=>'OAuthKeys',
        'bean_name'=>'OAuthKey',
        'source'=>'non-db',
      ),
      ),
    'indices' => array (
       'id'=>array('name' =>'oauthtokenpk', 'type' =>'primary', 'fields'=>array('id', 'deleted')),
       'state_ts'=>array('name' =>"oauth_state_ts", 'type' =>'index', 'fields'=>array('tstate','token_ts')),
    ),
   'relationships'=>array(
        'consumer' =>
           array('lhs_module'=> 'OAuthKeys', 'lhs_table'=> 'oauth_consumer', 'lhs_key' => 'id',
   				'rhs_module'=> 'OAuthTokens', 'rhs_table'=> 'oauth_tokens', 'rhs_key' => 'consumer',
   				'relationship_type'=>'one-to-many')
    )
);
if (!class_exists('VardefManager')){
        require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('OAuthTokens','OAuthToken', array('assignable'));

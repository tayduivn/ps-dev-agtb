<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: MyDCEReportsDashlet.data.php 24516 2007-07-20 22:05:19Z jenny $

global $current_user;

$dashletData['MyDCEReportsDashlet']['searchFields'] = array('time_start' => array('default' => 'TP_last_7_days'));                             
$dashletData['MyDCEReportsDashlet']['columns'] = array(
                                                        'instance_name' => 
                                                        array (
                                                            'width' => '8',
                                                            'label' => 'LBL_INSTANCE_NAME',
                                                            'default' => true,
                                                            'link' => true,
                                                            'module' => 'DCEInstances',
                                                        ),
                                                        'max_num_sessions' => 
                                                        array (
                                                            'label' => 'LBL_MAX_NUM_SESSIONS',
                                                            'default' => false,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        'num_of_logins' => 
                                                        array (
                                                            'label' => 'LBL_NUM_OF_LOGINS',
                                                            'default' => true,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        'num_of_requests' => 
                                                        array (
                                                            'label' => 'LBL_NUM_OF_REQUESTS',
                                                            'default' => true,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        'memory' => 
                                                        array (
                                                            'label' => 'LBL_MEMORY_UNIT',
                                                            'default' => true,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'convert'=>false ),
                                                            
                                                        ),
                                                        'num_of_files' => 
                                                        array (
                                                            'label' => 'LBL_NUM_OF_FILES',
                                                            'default' => true,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        'num_of_users' => 
                                                        array (
                                                            'label' => 'LBL_NUM_OF_USERS',
                                                            'default' => false,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        
                                                        'num_of_queries' => 
                                                        array (
                                                            'label' => 'LBL_NUM_OF_QUERIES',
                                                            'default' => true,
                                                            'width' => '8',
                                                            'currency_format' => array('currency_symbol'=>false, 'decimals'=>0, 'convert'=>false),
                                                        ),
                                                        'last_login_time' => 
                                                        array (
                                                            'width' => '8',
                                                            'label' => 'LBL_LAST_LOGIN_TIME',
                                                            'default' => false,
                                                        ),
                                                       );
?>

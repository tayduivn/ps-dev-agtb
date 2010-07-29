<?php
//FILE SUGARCRM flav!=sales ONLY
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

 // $Id: MyEmailsDashlet.data.php 17070 2006-10-13 22:09:18Z awu $

global $current_user, $app_strings;

$dashletData['MyEmailsDashlet']['searchFields'] = array(
												 	   'date_sent'  => array('default' => ''),
                                                       'name'  => array('default' => ''),
													   //'from_addr_name' => array('default' => ''),
                                                       //BEGIN SUGARCRM flav=pro ONLY
                                                       //'team_id'          => array('default' => '', 'label'=>'LBL_TEAMS'),
                                                       //END SUGARCRM flav=pro ONLY													
                                                       'assigned_user_id'   => array('default' => ''),
                                                       );
$dashletData['MyEmailsDashlet']['columns'] = array(
                                                   'from_addr' => array('width'   => '15',
                                                                       'label'   => 'LBL_FROM',
                                                                       'default' => true),
												   'name' => array('width'   => '40',
                                                                   'label'   => 'LBL_SUBJECT',
                                                                   'link'    => true,
                                                                   'default' => true),
                                                   'to_addrs' => array('width'   => '15',
                                                                         'label'   => 'LBL_TO_ADDRS',
                                                                         'default' => false),
                                                   'assigned_user_name' => array('width'   => '15',
                                                                         'label'   => 'LBL_LIST_ASSIGNED',
                                                                         'default' => false),

                                                   //BEGIN SUGARCRM flav=pro ONLY
                                                   'team_name' => array('width'   => '15',
                                                                        'label'   => 'LBL_LIST_TEAM',
                                                                        'sortable' => false),
                                                   //END SUGARCRM flav=pro ONLY
                                                   'date_sent' => array('width'   => '15',
                                                                         'label'   => 'LBL_DATE_SENT',
                                                                         'default' => true,
                                                                         'defaultOrderColumn' => array('sortOrder' => 'ASC')
                                                                         ),
                                                  'date_entered' => array('width'   => '15',
                                                                          'label'   => 'LBL_DATE_ENTERED'),
                                                  'date_modified' => array('width'   => '15',
                                                                           'label'   => 'LBL_DATE_MODIFIED'),
                                                  'quick_reply' => array('width'   => '15',
                                                                        'label'   => '',
                                                                        'sortable' => false,
                                                                        'default' => true),
                                                   'create_related' => array('width'   => '25',
                                                                        'label'   => '',
                                                                        'sortable' => false,
                                                                        'default' => true),
                                                                        );

?>

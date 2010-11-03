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

 // $Id: MyProjectTaskDashlet.data.php 56115 2010-04-26 17:08:09Z kjing $

global $current_user;

$dashletData['MyProjectTaskDashlet']['searchFields'] =  array(
                                                            'date_entered'     => array('default' => ''),                                    
                                                            'date_start'        => array('default' => ''),
                                                            'date_finish'         => array('default' => ''),
                                                             //BEGIN SUGARCRM flav=pro ONLY
                                                            'team_id'          => array('default' => '', 'label'=>'LBL_TEAMS'),
                                                             //END SUGARCRM flav=pro ONLY
															'assigned_user_id' => array('type'    => 'assigned_user_name',
																						'label'   => 'LBL_ASSIGNED_TO', 
                                                                                        'default' => $current_user->name),
                                                            'team_id'          => array('default' => ''),
                                                            );
$dashletData['MyProjectTaskDashlet']['columns'] = array('name' => array('width'   => '40', 
                                                                       'label'   => 'LBL_NAME',
                                                                       'link'    => true,
                                                                       'default' => true), 
                                                       'priority' => array('width'   => '20',
                                                                           'label'   => 'LBL_PRIORITY',
                                                                          ),
                                                       'date_finish' => array('width'   => '20',
                                                                           'label'   => 'LBL_DATE_FINISH',
                                                                           'default' => true),
                                                       'time_finish' => array('width' => '15',
                                                                           'label' => 'LBL_TIME_FINISH'),
                                                       'date_start' => array('width' => '15',
                                                                             'label' => 'LBL_DATE_START',
                                                                             'default' => true),
                                                       'time_start' => array('width' => '15',
                                                                             'label' => 'LBL_TIME_START'),
                                                       'percent_complete' => array('width' => '15',
                                                                             'label' => 'LBL_PERCENT_COMPLETE'),
                                                       'project_name' => array('width' => '30',
                                                                              'label' => 'LBL_PROJECT_NAME',
                                                                              'related_fields' => array('project_id')),
                                                       'milestone_flag' => array('width' => '10',
                                                                                 'label' => 'LBL_MILESTONE_FLAG'),
                                                       'date_entered' => array('width' => '15', 
                                                                               'label' => 'LBL_DATE_ENTERED',
                                                                               'default' => true),
                                                       'date_modified' => array('width' => '15', 
                                                                                'label' => 'LBL_DATE_MODIFIED'),    
                                                       'created_by' => array('width' => '8', 
                                                                             'label' => 'LBL_CREATED'),
                                                       'assigned_user_name' => array('width'   => '8', 
                                                                                     'label'   => 'LBL_LIST_ASSIGNED_USER'),
                                                       //BEGIN SUGARCRM flav=pro ONLY
                                                       'team_name' => array('width'   => '15', 
                                                                            'label'   => 'LBL_LIST_TEAM',
                                                                            'sortable' => false,),
                                                       //END SUGARCRM flav=pro ONLY
                                                                           );

?>

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

 // $Id: MyDCEActionsDashlet.data.php 24516 2007-07-20 22:05:19Z jenny $

global $current_user;

$dashletData['MyDCEActionsDashlet']['searchFields'] = array('date_entered'     => array('default' => ''),
                                                               'date_modified'    => array('default' => ''),
                                                               'start_date'    => array('default' => ''),
                                                               'type' => array('default' => ''),
                                                                //BEGIN SUGARCRM flav=pro ONLY
                                                               'team_id'          => array('default' => ''),
                                                                //END SUGARCRM flav=pro ONLY
                                                               'status'      => array('default' => ''
                                                               ),
                                                              );                             
$dashletData['MyDCEActionsDashlet']['columns'] = array(
                                                        'instance_name' => 
                                                        array (
                                                            'width' => '9',
                                                            'label' => 'LBL_INSTANCE_NAME',
                                                            'default' => true,
                                                            'module' => 'DCEInstances',
                                                            'id' => 'instance_id',
                                                            'link' => true,
                                                            'related_fields' => 
                                                            array (
                                                                0 => 'instance_id',
                                                            ),
                                                        ),
                                                        'cluster_name' => 
                                                         array (
                                                            'width' => '9',  
                                                            'label' => 'LBL_CLUSTER_NAME',
                                                            'default' => true,
                                                            'module' => 'DCEClusters',
                                                            'id' => 'cluster_id',
                                                            'link' => true,
                                                            'related_fields' => 
                                                            array (
                                                                0 => 'cluster_id',
                                                            ),
                                                        ),
                                                        'template_name' => 
                                                        array (
                                                            'width' => '9',  
                                                            'label' => 'LBL_TEMPLATE_NAME',
                                                            'default' => true,
                                                            'module' => 'DCETemplates',
                                                            'id' => 'template_id',
                                                            'link' => true,
                                                            'related_fields' => 
                                                            array (
                                                                0 => 'template_id',
                                                            ),
                                                        ),
                                                        'type' => 
                                                        array (
                                                            'label' => 'LBL_TYPE',
                                                            'default' => true,
                                                            'width' => '10',
                                                        ),
                                                        'status' => 
                                                        array (
                                                            'label' => 'LBL_STATUS',
                                                            'default' => true,
                                                            'width' => '10',
                                                        ),
                                                        'node' => 
                                                        array (
                                                            'label' => 'LBL_NODE',
                                                            'default' => true,
                                                            'width' => '10',
                                                        ),
                                                        'date_entered' => 
                                                        array (
                                                            'width' => '10',
                                                            'label' => 'LBL_DATE_ENTERED',
                                                            'default' => true,
                                                        ),
                                                        'date_completed' => 
                                                        array (
                                                            'width' => '10',
                                                            'label' => 'LBL_DATE_COMPLETED',
                                                            'default' => true,
                                                        ),
                                                       );
?>

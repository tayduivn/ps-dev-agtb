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
$viewdefs['DataSets']['DetailView'] = array(
    'templateMeta' => array('maxColumns' => '2',
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30')
                                            ),
    ),
 'panels' =>array (
  'default' =>
  array (

    array (
	  'name',
      array (
        'name' => 'report_name',
        'customCode' => '<a href="index.php?module=ReportMaker&action=DetailView&record={$fields.report_id.value}">{$fields.report_name.value}</a>',
      ),
    ),

    array (
      array('name'=>'query_name', 'type'=>'varchar'),
      'parent_name',
    ),

    array (
      array (
        'name' => 'child_name',
        'customCode' => '{if isset($bean->child_id) && !empty($bean->child_id)}
						 <a href="index.php?module=DataSets&action=DetailView&record={$bean->child_id}">{$bean->child_name}</a>
						 {else}
						 {$bean->child_name}
						 {/if}'
      ),
      'team_name',
    ),

    array (
      'description',
    ),

    array (

      array (
        'name' => 'table_width',
        'fields'=>array('table_width', 'table_width_type'),
        //'customCode' => '{$fields.table_width.value} {$APP.width_type_dom[$fields.table_width_type.value]}',
      ),
      'font_size',
    ),

    array (
      'exportable',
      'header_text_color',
    ),

    array (
      'header',
      'body_text_color',
    ),

    array (
      'prespace_y',
      'header_back_color',
    ),

    array (
      'use_prev_header',
      'body_back_color',
    ),


  ),
)


);
?>
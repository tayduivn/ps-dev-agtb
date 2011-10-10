<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('include/generic/SugarWidgets/SugarWidgetFieldtext.php');

class SugarWidgetFieldHtml extends SugarWidgetFieldText
{

	function _get_list_value(& $layout_def) {
		$key = '';
		$value = '';

		if (isset ($layout_def['varname'])) {
			$key = strtoupper($layout_def['varname']);
		} else {
			$key = $this->_get_column_alias($layout_def);
			$key = strtoupper($key);
		}

		if($layout_def['type'] == "html"){
			if(isset($this->layout_manager->defs["reporter"]->focus->field_name_map[$layout_def['name']]["default_value"])){
				return $this->layout_manager->defs["reporter"]->focus->field_name_map[$layout_def['name']]["default_value"];
			} else {
				return "";
			}
		}		

		if (isset ($layout_def['fields'][$key])) {
			return $layout_def['fields'][$key];
		}
		return $value;

	}
	
	function displayHeaderCell($layout_def)
	{
				global $start_link_wrapper,$end_link_wrapper;


                // don't show sort links if name isn't defined
                $no_sort = $this->layout_manager->getAttribute('no_sort');
                if(empty($layout_def['name']) || ! empty($no_sort) || ! empty($layout_def['no_sort']))
                {
                        return $layout_def['label'];
                }



                $sort_by ='';
                if ( ! empty($layout_def['table_key']) && ! empty($layout_def['name']) )
                {
                  if (! empty($layout_def['group_function']) && $layout_def['group_function'] == 'count')
                  {
                    $sort_by = 'count';
                  } else {
                        	$sort_by = $layout_def['table_key'].":".$layout_def['name'];
                          if ( ! empty($layout_def['column_function']))
                          {
                            $sort_by .= ':'.$layout_def['column_function'];
                          } else if ( ! empty($layout_def['group_function']) )
                        	{
                             $sort_by .= ':'.$layout_def['group_function'];
                        	}
                  }
                }
                else
                {
                        return $this->displayHeaderCellPlain($layout_def);
                }

                $start = $start_link_wrapper;
                $end = $end_link_wrapper;

                $start = empty($start) ? '': $start;
                $end = empty($end) ? '': $end;

                // unable to retrieve the vardef here, exclude columns of type clob/text from being sortable

                if(!in_array($layout_def['name'], array('description', 'account_description', 'lead_source_description', 'status_description', 'to_addrs', 'cc_addrs', 'bcc_addrs', 'work_log', 'objective', 'resolution')) && $this->layout_manager->defs["reporter"]->focus->field_name_map[$layout_def['name']]["type"] != "html") {
                    $header_cell = "<a class=\"listViewThLinkS1\" href=\"".$start.$sort_by.$end."\">";
                    $header_cell .= $this->displayHeaderCellPlain($layout_def);
                    $imgArrow = '';

                    if (isset($layout_def['sort']))
                    {
                            $imgArrow = $layout_def['sort'];
                    }
                    $arrow_start = ListView::getArrowUpDownStart($imgArrow);
                    $arrow_end = ListView::getArrowEnd();
                    $header_cell .= ' ' . $arrow_start.$arrow_end."</a>";
                }
                else {
                    $header_cell = $this->displayHeaderCellPlain($layout_def);
                }

                return $header_cell;
        }	
	
}



?>

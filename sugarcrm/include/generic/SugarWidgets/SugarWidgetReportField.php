<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * SugarWidgetField
 *
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: SugarWidgetReportField.php 58357 2010-09-29 23:22:03Z kjing $

require_once('include/generic/SugarWidgets/SugarWidgetField.php');

$used_aliases = array();
$alias_map = array();

class SugarWidgetReportField extends SugarWidgetField
{
	function SugarWidgetReportField(&$layout_manager) {
        parent::SugarWidgetField($layout_manager);
    }

	function  getSubClass($layout_def)
	{
		if (! empty($layout_def['type']))
		{

			if ($layout_def['type'] == 'time') {
				$layout_def['widget_class'] = 'Fielddate';
			} else {
				$layout_def['widget_class'] = 'Field'.$layout_def['type'];
			}
			return $this->layout_manager->getClassFromWidgetDef($layout_def);
		} else {
			return $this;
		}
	}


 function display($layout_def)
 {
        $obj = $this->getSubClass($layout_def);
		
	   //Bug40995
	   $precision=$obj->layout_manager->defs['reporter']->focus->field_name_map[$layout_def['name']]['precision'];
	   $layout_def['precision']=$precision;
	   //Bug40995


        $context = $this->layout_manager->getAttribute('context');//_ppd($context);
        $func_name = 'display'.$context;


        if ( ! empty($context) && method_exists($obj,$func_name))
        {
                return  $obj->$func_name($layout_def);
        } else
        {
                return 'display not found:'.$func_name;
        }
 }



 function _get_column_select_special($layout_def)
 {
 		$alias = '';
		 if ( ! empty($layout_def['table_alias']))
		 {
			$alias = $layout_def['table_alias'];
		 }

		$reporter = $this->layout_manager->getAttribute("reporter");

    if ($layout_def['name'] == 'weighted_sum' )
    {
//BEGIN SUGARCRM flav=ent ONLY
			if ( $reporter->db->dbType == 'oci8')
			{
				return "SUM( NVL(".$alias.".probability,0) * NVL(".$alias.".amount_usdollar,0) * 0.01) ";
			} else {
//END SUGARCRM flav=ent ONLY
				return "SUM( ".$alias.".probability * ".$alias.".amount_usdollar * 0.01) ";
//BEGIN SUGARCRM flav=ent ONLY
			}
//END SUGARCRM flav=ent ONLY
		}
    if ($layout_def['name'] == 'weighted_amount' )
    {
//BEGIN SUGARCRM flav=ent ONLY
			if ( $reporter->db->dbType == 'oci8')
			{
				return "AVG( NVL(".$alias.".probability,0) * NVL(".$alias.".amount_usdollar,0) * 0.01) ";
			} else {
//END SUGARCRM flav=ent ONLY
				return "AVG(".$alias.".probability * ".$alias.".amount_usdollar * 0.01) ";
//BEGIN SUGARCRM flav=ent ONLY
			}
//END SUGARCRM flav=ent ONLY
		}
 }

 function _get_column_select($layout_def)
 {
 	global $reportAlias;
 	if (!isset($reportAlias)) {
 		$reportAlias = array();
 	}
		$alias = '';
		$endalias = '';

	if ( ! empty($layout_def['group_function']) )
	{
    	if ($layout_def['name'] == 'weighted_sum' || $layout_def['name'] == 'weighted_amount')
    	{
    			$alias = $this->_get_column_select_special($layout_def);
    			$reportAlias[$alias] = $layout_def;
				return $alias;
    	}

			$reporter = $this->layout_manager->getAttribute('reporter');
//BEGIN SUGARCRM flav=ent ONLY
			if ($reporter->db->dbType == 'oci8')
			{
				$alias .= $layout_def['group_function']."(NVL(";
				$endalias = ',0))';
			} else {
//END SUGARCRM flav=ent ONLY
				$alias .= $layout_def['group_function']."(";
				$endalias = ')';
//BEGIN SUGARCRM flav=ent ONLY
			}
//END SUGARCRM flav=ent ONLY
	}
	if ( ! empty($layout_def['table_alias']))
	{
		$alias .= $layout_def['table_alias'].".".$layout_def['name'];

	}else if (! empty($layout_def['name'])) {
		$alias = $layout_def['name'];
	} else {
		$alias .= "*";
	}
	$alias .= $endalias;

	$reportAlias[$alias] = $layout_def;
	return $alias;
 }

 function querySelect(&$layout_def)
 {
			return $this->_get_column_select($layout_def)." ".$this->_get_column_alias($layout_def)."\n";
 }

 function queryGroupBy($layout_def)
 {
	return $this->_get_column_select($layout_def)." \n";
 }


 function queryOrderBy($layout_def)
 {
	$reporter = $this->layout_manager->getAttribute('reporter');
	if(!empty($reporter->all_fields[$layout_def['column_key']])) $field_def = $reporter->all_fields[$layout_def['column_key']];

	if ( ! empty( $field_def['sort_on']))
	{
			$order_by = $layout_def['table_alias'].".".$field_def['sort_on'];
            if(!empty($field_def['sort_on2']))
                $order_by .= ', ' . $layout_def['table_alias'].".".$field_def['sort_on2'];
    }
	else {
		$order_by = $this->_get_column_alias($layout_def)." \n";
	}

			if ( empty($layout_def['sort_dir']) || $layout_def['sort_dir'] == 'a')
			{
				return $order_by." ASC";
			} else {
				return $order_by." DESC";
			}
 }


 function queryFilter($layout_def)
 {
	$method_name = "queryFilter".$layout_def['qualifier_name'];
	return $this->$method_name($layout_def);
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

                if(!in_array($layout_def['name'], array('description', 'account_description', 'lead_source_description', 'status_description', 'to_addrs', 'cc_addrs', 'bcc_addrs', 'work_log', 'objective', 'resolution'))) {
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

	function query($layout_def)
 	{
       		 $obj = $this->getSubClass($layout_def);

        	$context = $this->layout_manager->getAttribute('context');
       	 	$func_name = 'query'.$context;

        	if ( ! empty($context) && method_exists($obj,$func_name))
       		 {
               		 return  $obj->$func_name($layout_def);
        	} else
        	{
                	return '';
		}
 	}

 function _get_column_alias($layout_def)
 {
        $alias_arr = array();

				if ($layout_def['table_key'] == 'self' && !empty($layout_def['name']) && $layout_def['name'] == 'id')
				{
					return 'primaryid';
				}

        if ( ! empty($layout_def['group_function']) && $layout_def['group_function']=='count')
        {
                return 'count';
        }

        if ( ! empty($layout_def['table_alias']))
        {
                array_push($alias_arr,$layout_def['table_alias']);
        }

        if ( ! empty($layout_def['group_function']) && $layout_def['group_function'] != 'weighted_amount' && $layout_def['group_function'] != 'weighted_sum')
        {
                array_push($alias_arr,$layout_def['group_function']);
        } else if ( ! empty($layout_def['column_function']))
        {
                array_push($alias_arr,$layout_def['column_function']);
        } else if ( ! empty($layout_def['qualifier']))
        {
                array_push($alias_arr,$layout_def['qualifier']);
        }

        if ( ! empty($layout_def['name']))
        {
                array_push($alias_arr,$layout_def['name']);
        }

		global $used_aliases, $alias_map;

        $alias = strtolower(implode("_",$alias_arr));
        
        $short_alias = $this->getTruncatedColumnAlias($alias);

		if ( empty($used_aliases[$short_alias]))
		{
			$alias_map[$alias] = $short_alias;
		    $used_aliases[$short_alias] = 1;
          	return $short_alias;
		} else if ( ! empty($alias_map[$alias]) )
		{
			return $alias_map[$alias];
		} else {
			$alias_map[$alias] = $short_alias.'_'.$used_aliases[$short_alias];
		  $used_aliases[$short_alias]++;
			return $alias_map[$alias];
		}
 }

 function queryFilterEmpty(&$layout_def)
 {
	 $reporter = $this->layout_manager->getAttribute("reporter");
	 if( $reporter->db->dbType == 'mssql' && $layout_def['type'] == 'currency')
     {
    	 return '( '.$this->_get_column_select($layout_def).' IS NULL OR '.$this->_get_column_select($layout_def)."=0 )\n";
     }
     return '( '.$this->_get_column_select($layout_def).' IS NULL OR '.$this->_get_column_select($layout_def)."='' )\n";
 }

 function queryFilterIs(&$layout_def)
 {
 	return '( '.$this->_get_column_select($layout_def)."='".$GLOBALS['db']->quote($layout_def['input_name0'])."')\n";
 }

  function queryFilteris_not(&$layout_def)
 {
 	return '( '.$this->_get_column_select($layout_def)."<>'".$GLOBALS['db']->quote($layout_def['input_name0'])."')\n";
 }
 function queryFilterNot_Empty(&$layout_def)
 {
    $reporter = $this->layout_manager->getAttribute("reporter");
//BEGIN SUGARCRM flav=ent ONLY
    if ( $reporter->db->dbType == 'oci8')
    {
        return $this->_get_column_select($layout_def).' IS NOT NULL ' . "\n";
    }

//END SUGARCRM flav=ent ONLY
    if( $reporter->db->dbType == 'mssql' && $layout_def['type'] == 'currency')
    {
    	return '( '.$this->_get_column_select($layout_def).' IS NOT NULL AND '.$this->_get_column_select($layout_def)."<>0 )\n";
    }

    return '( '.$this->_get_column_select($layout_def).' IS NOT NULL AND '.$this->_get_column_select($layout_def)."<>'' )\n";
 }

}
?>

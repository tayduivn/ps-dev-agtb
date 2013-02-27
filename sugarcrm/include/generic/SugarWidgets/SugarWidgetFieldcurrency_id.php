<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class SugarWidgetFieldcurrency_id extends SugarWidgetFieldEnum
{
    /**
     * Returns list of beans of currencies including default system currency
     *
     * @param bool $refresh cache
     * @return array list of beans
     */
    static public function getCurrenciesList($refresh = false)
    {
        static $list = false;
        if ($list === false || $refresh == true)
        {
            $currency = new Currency();
            $list = $currency->get_full_list('name');
            $currency->retrieve('-99');
            if (is_array($list))
            {
                $list = array_merge(array($currency), $list);
            }
            else
            {
                $list = array($currency);
            }
        }
        return $list;
    }

    /**
     * Overriding display of value of currency because of currencies are not stored in app_list_strings
     *
     * @param array $layout_def
     * @return string for display
     */
    public function &displayListPlain($layout_def)
    {
        static $currencies;
        $value = $this->_get_list_value($layout_def);
        if (empty($currencies[$value]))
        {
            $currency = new Currency();
            $currency->retrieve($value);
            $currencies[$value] = $currency->symbol . ' ' . $currency->iso4217;
        }
        return $currencies[$value];
    }

    /**
     * Overriding sorting because of default currency is not present in DB
     *
     * @param array $layout_def
     * @return string for order by
     */
    public function queryOrderBy($layout_def)
    {
        $tmpList = self::getCurrenciesList();
        $list = array();
        foreach ($tmpList as $bean)
        {
            $list[$bean->id] = $bean->symbol . ' ' . $bean->iso4217;
        }

        $field_def = $this->reporter->all_fields[$layout_def['column_key']];
        if (!empty ($field_def['sort_on']))
        {
            $order_by = $layout_def['table_alias'].".".$field_def['sort_on'];
        }
        else
        {
            $order_by = $this->_get_column_select($layout_def);
        }

        if (empty ($layout_def['sort_dir']) || $layout_def['sort_dir'] == 'a')
        {
            $order_dir = "ASC";
        }
        else
        {
            $order_dir = "DESC";
        }
        return $this->reporter->db->orderByEnum($order_by, $list, $order_dir);
    }
}

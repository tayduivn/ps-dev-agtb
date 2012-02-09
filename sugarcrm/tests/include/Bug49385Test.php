<?php
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

class Bug49385Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function providerData()
    {
        $return = array();
        foreach ( $GLOBALS['beanList'] as $key => $value )
        {
            $return[] = array($value, isset($GLOBALS['beanFiles'][$value]) ? $GLOBALS['beanFiles'][$value] : null, $GLOBALS['current_user']);
        }
//        $return = array(
//            array('Cases', 'aCase', 'modules/Cases/Case.php', $GLOBALS['current_user'])
//        );
        return $return;
    }

    /**
     * @dataProvider providerData
     */
    public function testCreateNewListQuery($bean, $bean_path, $current_user)
    {
        $GLOBALS['current_user'] = $current_user;
        $order_by = '';
        $where = '';
        $filter = array();
        $params = array();
        $show_deleted = 0;
        $join_type = '';
        $return_array = true;
        $parent_bean = null;
        $singleSelect = true;
        $ifListForExport = false;

        if ( null !== $bean_path )
        {
            require_once($bean_path);
            $objBean = new $bean();
            $parent_bean = $objBean;

            $this->doTest($bean, $objBean, $order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parent_bean, $singleSelect, $ifListForExport);
        }

        unset($GLOBALS['current_user']);
    }

    private function doTest($bean, $objBean, $order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parent_bean, $singleSelect, $ifListForExport)
    {
        $related_fields = $objBean->get_related_fields();
        if ( !empty($related_fields) )
        {
            $related_m2m_fields = array();
            foreach ( $related_fields as $related_field => $data )
            {
                if($data['type'] == 'relate' && isset($data['link']))
                {
                    $objBean->load_relationship($data['link']);
                    if( !empty($objBean->$data['link']) && $objBean->$data['link']->relationship->type == 'many-to-many' )
                    {
                        $related_m2m_fields[$related_field] = $data;
                    }
                }
            }

            if ( !empty($related_m2m_fields) )
            {
                $ret_array = $objBean->create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parent_bean, $singleSelect, $ifListForExport);
                // check if many-to-many query was generated
                $this->assertNotEmpty($ret_array['many_to_many_query']);

                foreach ( $related_m2m_fields as $related_field => $data  )
                {
                    // check if many-to-many query for current relationship was generated and has correct structure
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]);
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]['query']);
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]['rel_key']);
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]['rel_module']);
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]['bean_key']);
                    $this->assertNotEmpty($ret_array['many_to_many_query'][$related_field]['bean_module']);
                    $this->assertArrayHasKey('query_where', $ret_array['many_to_many_query'][$related_field]);

                    // check is query valid SQL
                    $query = $ret_array['many_to_many_query'][$related_field]['query'] . ' AND '.$objBean->getTableName().".id IN ('00000-00000-00000')";
                    if ( isset($ret_array['many_to_many_query'][$related_field]['query_where']) && !empty($ret_array['many_to_many_query'][$related_field]['query_where']) )
                    {
                        $query .= ' AND ' . $ret_array['many_to_many_query'][$related_field]['query_where'];
                    }
                    $result = $GLOBALS['db']->query($query);
                    $this->assertNotEmpty($result);
                }

                $ret_array['inner_join'] = '';
                if (!empty($objBean->listview_inner_join)) {
                    $ret_array['inner_join'] = ' ' . implode(' ', $objBean->listview_inner_join) . ' ';
                }
                // check is main query valid SQL
                $main_query = $ret_array['select'].$ret_array['from'].$ret_array['inner_join'].$ret_array['where'].$ret_array['order_by'];
                $result = $GLOBALS['db']->query($main_query);
                $this->assertNotEmpty($result);

            } else {
                $this->markTestSkipped('There are not many-to-many relationships in the '.$bean);
            }

        } else {
            $this->markTestSkipped('There are not relationships in the '.$bean);
        }
    }
}

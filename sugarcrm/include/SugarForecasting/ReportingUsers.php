<?php
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

require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_ReportingUsers extends SugarForecasting_AbstractForecast
{
    /**
     * Process to get an array of Users for the user that was passed in
     *
     * @return array|string
     */
    public function process()
    {

        // check if the current user is a manager, if they are not, we will load up their reports to
        // as the starting user
        $getReportsTo = (!User::isManager($this->getArg('user_id')));

        /* @var $userBean User */
        $userBean = BeanFactory::getBean('Users', $this->getArg('user_id'));

        if($getReportsTo === true) {
            $userBean = BeanFactory::getBean('Users', $userBean->reports_to_id);
            $this->setArg('user_id', $userBean->id);
        }

        $user = array(
            'id' => $userBean->id,
            'first_name' => $userBean->first_name,
            'last_name' => $userBean->last_name,
            'user_name' => $userBean->user_name,
            'reports_to_id' => $userBean->reports_to_id,
            'title' => $userBean->title,
            'children' => array()
        );

        if (User::isManager($userBean->id)) {
            $user['children'] = $this->getChildren();
        }

        $tree = $this->formatForTree($user);

        if ($GLOBALS['current_user']->id != $this->getArg('user_id')) {
            // we need to create a parent record
            if (!empty($userBean->reports_to_id)) {
                $parent = $this->getParentLink($userBean->reports_to_id);
                // the open user should be marked as a manager now
                $tree['attr']['rel'] = 'manager';

                // put the parent link and the tree in the same level
                $tree = array($parent, $tree);
            }
        }

        return $tree;
    }

    /**
     * Load up all the reporting users for a give user
     *
     * @return array
     */
    protected function getChildren()
    {
        $additional_fields = array(
            'user_name',
            'first_name',
            'last_name',
            'reports_to_id',
            'title'
        );

        return User::getReporteesWithLeafCount($this->getArg('user_id'), false, $additional_fields);
    }

    /**
     * Format the main part of the tree
     *
     * @param $data
     * @return array
     */
    protected function formatForTree($data)
    {
        $tree = $this->getTreeArray(
            $data['id'],
            $data['first_name'],
            $data['last_name'],
            $data['user_name'],
            $data['reports_to_id'],
            $data['title'],
            'root'
        );

        if (isset($data['children']) && !empty($data['children'])) {
            // we have children
            // add the manager again as the my opportunities bunch
            $tree['children'][] = $this->getTreeArray(
                $data['id'],
                $data['first_name'],
                $data['last_name'],
                $data['user_name'],
                $data['reports_to_id'],
                $data['title'],
                'my_opportunities'
            );

            foreach ($data['children'] as $child) {
                $tree['children'][] = $this->getTreeArray(
                    $child['id'],
                    $child['first_name'],
                    $child['last_name'],
                    $child['user_name'],
                    $child['reports_to_id'],
                    $data['title']
                );
            }

            $tree['state'] = 'open';
        }

        return $tree;
    }

    /**
     * Utility method to get the Parent Link
     *
     * @param string $manager_reports_to
     * @return array
     */
    protected function getParentLink($manager_reports_to)
    {
        /* @var $parentBean User */
        $parentBean = BeanFactory::getBean('Users', $manager_reports_to);
        $parent = $this->getTreeArray(
            $parentBean->id,
            $parentBean->first_name,
            $parentBean->last_name,
            $parentBean->user_name,
            $parentBean->reports_to_id,
            $parentBean->title,
            'parent_link'
        );

        global $current_language;
        $current_module_strings = return_module_language($current_language, 'Forecasts');
        $parent['data'] = $current_module_strings['LBL_TREE_PARENT'];

        // overwrite the whole attr array for the parent
        $parent['attr'] = array(
            'rel' => 'parent_link',
            'class' => 'parent',
            // adding id tag for QA's voodoo tests
            'id' => 'jstree_node_parent'
        );

        return $parent;
    }

    /**
     * Utility method to build out a tree node array
     *
     * @param string $id
     * @param string $first_name
     * @param string $last_name
     * @param string $user_name
     * @param string $reports_to_id
     * @param string $rel
     * @return array
     */
    protected function getTreeArray($id, $first_name, $last_name, $user_name, $reports_to_id, $title, $rel = 'rep')
    {
        global $locale;
        $fullName = $locale->getLocaleFormattedName($first_name, $last_name);

        $qa_id = 'jstree_node_';
        if ($rel == "my_opportunities") {
            $qa_id .= 'myopps_';
        }

        $state = '';

        if ($rel == 'rep' && User::isManager($id)) {
            // check if the user is a manager and if they are change the rel to be 'manager'
            $rel = 'manager';
            $state = 'closed';
        }

        return array(
            'data' => $fullName,
            'children' => array(),
            'metadata' => array(
                'id' => $id,
                'user_name' => $user_name,
                'full_name' => $fullName,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'reports_to_id' => $reports_to_id,
                'title' => $title,
            ),
            'state' => $state,
            'attr' => array(
                // set all users to rep by default
                'rel' => $rel,
                // adding id tag for QA's voodoo tests
                'id' => $qa_id . $user_name
            )
        );
    }

}

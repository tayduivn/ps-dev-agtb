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

        if (User::isManager($userBean->id)) {
            $children = $this->getChildren($userBean);
        } else {
            $children = array();
        }

        $tree = $this->formatForTree($userBean, $children);

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
     * Load up all the reporting users for a given user
     * @param User $user
     * @return array
     */
    protected function getChildren(User $user)
    {
        $query = $user->create_new_list_query('',
            'users.reports_to_id = ' . $user->db->quoted($user->id) . ' AND users.status = \'Active\'');
        $response = $user->process_list_query($query, 0);
        return $response['list'];
    }

    /**
     * Format the main part of the tree
     * @param User $user
     * @param array $children
     * @return array
     */
    protected function formatForTree(User $user, array $children)
    {
        $tree = $this->getTreeArray($user, 'root');

        if (!empty($children)) {
            // we have children
            // add the manager again as the my opportunities bunch
            $tree['children'][] = $this->getTreeArray($user, 'my_opportunities');
            foreach ($children as $child) {
                $tree['children'][] = $this->getTreeArray($child, 'rep');
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
        $parent = $this->getTreeArray($parentBean, 'parent_link');

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
     * @param User $user
     * @param string $rel
     * @return array
     */
    protected function getTreeArray(User $user, $rel = 'rep')
    {
        global $locale;
        $fullName = $locale->formatName($user);

        $qa_id = 'jstree_node_';
        if ($rel == "my_opportunities") {
            $qa_id .= 'myopps_';
        }

        $state = '';

        if ($rel == 'rep' && User::isManager($user->id)) {
            // check if the user is a manager and if they are change the rel to be 'manager'
            $rel = 'manager';
            $state = 'closed';
        }

        return array(
            'data' => $fullName,
            'children' => array(),
            'metadata' => array(
                'id' => $user->id,
                'user_name' => $user->user_name,
                'full_name' => $fullName,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'reports_to_id' => $user->reports_to_id,
                'reports_to_name' => $user->reports_to_name,
                'title' => $user->title,
            ),
            'state' => $state,
            'attr' => array(
                // set all users to rep by default
                'rel' => $rel,
                // adding id tag for QA's voodoo tests
                'id' => $qa_id . $user->user_name
            )
        );
    }

}

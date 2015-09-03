<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'data/acl/SugarACLCategories.php';
require_once 'modules/ACLActions/actiondefs.php';

class SugarACLCategoriesTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $module = 'Categories';

    /**
     * @var string
     */
    protected $moduleOriginal = 'KBContents';

    /**
     * @var SugarACLCategories
     */
    protected $acl;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $oldCurrentUser;

    /**
     * @var array
     */
    protected $defaultUserActions = array();

    public function setUp()
    {
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->acl = new SugarACLCategories(array('aclModule' => $this->moduleOriginal));

        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $this->defaultUserActions = ACLAction::getUserActions($this->user->id, true, $this->moduleOriginal);
    }

    public function tearDown()
    {
        $aclActions = new ACLAction();
        $aclActions->clearACLCache();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['current_user'] = $this->oldCurrentUser;
        SugarTestCategoryUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider aclDataProvider
     */
    public function testNoBeanACL($access, $actions, $results)
    {
        $aclData = $this->defaultUserActions[$this->moduleOriginal];

        foreach ($actions as $action) {
            $aclData['module'][$action]['aclaccess'] = $access;
            ACLAction::setACLData($this->user->id, $this->moduleOriginal, $aclData);
            foreach ($results as $result) {
                list($isOwner, $permissions) = $result;
                $context = array('user' => $this->user, 'owner_override' => $isOwner);

                $actualUserAccess = $this->acl->getUserAccess(
                    $this->module,
                    array($action => true),
                    $context
                );
                $actualAccess = $this->acl->checkAccess($this->module, $action, $context);

                $this->assertEquals($permissions, $actualUserAccess[$action]);
                $this->assertEquals($permissions, $actualAccess);
            }
        }
    }

    /**
     * @dataProvider aclBeanDataProvider
     */
    public function testWithBeanACL($access, $actions, $results)
    {
        /**
         * Note:
         * if bean sets - we will check is_owner for bean and apply it for checkAccessInternal method
         * if no bean - we will set is_owner based on context['owner_override'] parameter
         */
        $aclData = $this->defaultUserActions[$this->moduleOriginal];
        foreach ($actions as $action) {
            $aclData['module'][$action]['aclaccess'] = $access;
            ACLAction::setACLData($this->user->id, $this->moduleOriginal, $aclData);
            foreach ($results as $result) {
                list($isOwner, $permissions) = $result;
                $values = array();
                $categoryBean = SugarTestCategoryUtilities::createBean($values);
                if (!$isOwner) {
                    $assignedUser = SugarTestUserUtilities::createAnonymousUser();
                    $categoryBean->created_by = $assignedUser->id;
                    $categoryBean->save();
                }
                $context = array('user' => $this->user, 'bean' => $categoryBean);

                $actualUserAccess = $this->acl->getUserAccess(
                    $this->module,
                    array($action => true),
                    $context
                );
                $actualAccess = $this->acl->checkAccess($this->module, $action, $context);

                $this->assertEquals($permissions, $actualUserAccess[$action]);
                $this->assertEquals($permissions, $actualAccess);
            }
        }
    }

    public function aclDataProvider()
    {
        /**
         * @var int $access
         * @var array $actions
         * @var array $results
         */
        return array(
            array(
                ACL_ALLOW_ALL, // Checked permission.
                array('delete', 'edit', 'export', 'list', 'view', 'import', 'massupdate'), // Checked actions.
                array( // Expected results
                    array( // Expected result if owner is true.
                        true, // isOwner
                        true // Result
                    ),
                    array( // Expected result if owner is false.
                        false,
                        true
                    )
                )
            ),
            array(
                ACL_ALLOW_OWNER,
                array('delete', 'edit', 'export', 'list', 'view'),
                array(
                    array(true, true),
                    array(false, false)
                ),
            ),
            array(
                ACL_ALLOW_NONE,
                array('delete', 'edit', 'export', 'list', 'view', 'import', 'massupdate'),
                array(
                    array(true, false),
                    array(false, false)
                )
            ),
            array(
                ACL_ALLOW_ENABLED,
                array('access'),
                array(
                    array(true, true),
                    array(false, true,)
                )
            ),
            array(
                ACL_ALLOW_DISABLED,
                array('access'),
                array(
                    array(true, false),
                    array(false, false,)
                )
            )
        );
    }

    public function aclBeanDataProvider()
    {
        /**
         * @var int $access
         * @var array $actions
         * @var array $results
         */
        return array(
            array(
                ACL_ALLOW_OWNER,
                array('delete', 'edit', 'view'),
                array(
                    array(true, true),
                    array(false, false)
                ),
            )
        );
    }
}

<?php
// FILE SUGARCRM flav=pro && flav!=ent ONLY
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
namespace Sugarcrm\SugarcrmTestUnit\modules\ReportSchedules\upgrade\scripts\post;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

require_once 'modules/ReportSchedules/upgrade/scripts/post/9_RemoveRSCopyFromPro.php';

/**
 * @coversDefaultClass \SugarUpgradeRemoveRSCopyFromPro
 */
class SugarUpgradeRemoveRSCopyFromProTest extends TestCase
{
    /**
     * Data provider for testRemoveCopyButton()
     * @return array
     */
    public function providerTestRemoveCopyButton()
    {
        return array(
            // copy button exists
            array(
                array(
                    'buttons' => array(
                        array(
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => array(
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:duplicate_button:click',
                                    'name' => 'duplicate_button',
                                    'label' => 'LBL_DUPLICATE_BUTTON_LABEL',
                                    'acl_action' => 'create',
                                ),
                            ),
                        ),
                    ),
                ),
                true,
            ),
            // copy button doesn't exist
            array(
                array(
                    'buttons' => array(
                        array(
                            'type' => 'actiondropdown',
                            'name' => 'main_dropdown',
                            'primary' => true,
                            'showOn' => 'view',
                            'buttons' => array(
                                array(
                                    'type' => 'rowaction',
                                    'event' => 'button:edit_button:click',
                                    'name' => 'edit_button',
                                    'label' => 'LBL_EDIT_BUTTON_LABEL',
                                    'primary' => true,
                                    'acl_action' => 'edit',
                                ),
                            ),
                        ),
                    ),
                ),
                false,
            ),
        );
    }

    /**
     * @covers ::removeCopyButton
     * @dataProvider providerTestRemoveCopyButton
     */
    public function testRemoveCopyButton($meta, $expected)
    {
        $mock = $this->createMock('\SugarUpgradeRemoveRSCopyFromPro');
        $result = TestReflection::callProtectedMethod($mock, 'removeCopyButton', array(&$meta));
        $this->assertEquals($expected, $result);
    }
}

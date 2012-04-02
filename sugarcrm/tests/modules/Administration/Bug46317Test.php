<?php
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
require_once 'modules/Administration/updater_utils.php';

/**
 * Bug #46317
 * Automatically Check For Updates issue
 * @ticket 46317
 */
class Bug46317Test extends Sugar_PHPUnit_Framework_TestCase
{

    function versionProvider()
    {
        return array(
            array('6.3.1', '6_3_0', TRUE),
            array('6.4', '6.3.1', TRUE),
            array('6_4_0', '6.3.10', TRUE),
            array('6_3_1', '6.3.1', FALSE),
            array('6.3.0', '6_4', FALSE),
            array('6.4.0RC3', '6.3.1', TRUE),
            array('6.4.0RC3', '6.3.1.RC4', TRUE),
            array('goober', 'noober', FALSE),
            array('6.3.5b', 'noob', TRUE),
            array('noob', '6.3.5b', FALSE),
            array('6.5.0beta2', '6.5.0beta1', TRUE),
            array('6.5.5.5.5', '7.5.5.5.5', FALSE),
            array('6.3', '6.2.3.4.5.2.5.2.4superalpha', TRUE),
            array('000000000000.1', '000000000000.1', FALSE),
            array('000000000000.1', '000000000000.05', FALSE),
            array('000000000000.05', '000000000000.1', TRUE),
        );
    }

    /**
     * @dataProvider versionProvider
     * @group 46317
     */
    function testCompareVersions($last_version, $current_version, $expectedResult)
    {
        $this->assertEquals($expectedResult, compareVersions($last_version, $current_version), "Current version: $current_version, last available version: $last_version");
    }
}
?>
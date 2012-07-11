<?php
//FILE SUGARCRM flav=ent ONLY

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once("include/Expressions/Dependency.php");
require_once("include/Expressions/Trigger.php");
require_once("include/Expressions/Expression/Parser/Parser.php");
require_once("include/Expressions/Actions/ActionFactory.php");

class CalculateCommitStageValueTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function testSetValues()
    {
        //Test calculated opportunity's field 'commit_stage'
        $opp = new Opportunity();
        $target = "commit_stage";
        $expr = 'ifElse(equal($forecast,1),"Include",ifElse(greaterThan($probability,50),"Likely","Omit"))';

        //commit stage should be "Include"
        $opp->forecast = 1;
        $action = ActionFactory::getNewAction("SetValue", array("target" => $target,"value" => $expr));
        $action->fire($opp);
        $this->assertEquals($opp->$target, "Include", "commit stage should be 'Included'");

        //commit stage should be "Omit"
        $opp->forecast = 0;
        $opp->probability = 40;
        $action->fire($opp);
        $this->assertEquals($opp->$target, "Omit", "commit stage should be 'Omit'");

        //commit stage should be "Likely"
        $opp->probability = 60;
        $action->fire($opp);
        $this->assertEquals($opp->$target, "Likely", "commit stage should be 'Likely'");
    }
}
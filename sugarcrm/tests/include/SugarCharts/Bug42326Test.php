<?php
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

require_once('include/SugarCharts/SugarChartFactory.php');

class Bug42326Test extends Sugar_PHPUnit_Framework_TestCase
{
    private $sugarChart;

	public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->sugarChart = SugarChartFactory::getInstance('Jit', 'Reports');
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @dataProvider xmlDataBuilder
     */
    public function testStackedBarChartHasCorrectLabelJSON($xmldata, $expectedjson) {
        $json = $this->sugarChart->buildLabelsBarChart($xmldata);
        $this->assertSame($expectedjson, $json);
    }

    public function xmlDataBuilder() {
        $dataset = array(
            // check labels for regression of normal bar chart
            array('<?xml version="1.0" encoding="UTF-8"?><sugarcharts version="1.0"><data><group><title>Label1</title><value>4</value><label>4</label><subgroups></subgroups></group><group><title>Label2</title><value>3</value><label>3</label><subgroups></subgroups></group></data></sugarcharts>',
                  "\t\"label\": [\n\n\t\t\"Label1\"\n,\n\t\t\"Label2\"\n\n\t],\n\n",),

            // check labels on stacked bar chart generate correct JSON
            // before the fix, this would have resulted in "\t'label': [\n\n\t\t'Name1'\n],\n\n"
            array('﻿<?xml version="1.0" encoding="UTF-8"?><sugarcharts version="1.0"><data><group><title>Name1</title><value>1</value><label>1</label><subgroups><group><title>Label1</title><value>1</value><label>1</label><link></link></group><group><title>Label2</title><value>NULL</value><label></label><link></link></group></subgroups></group></data></sugarcharts>',
                  "\t\"label\": [\n\n\t\t\"Label1\"\n,\n\t\t\"Label2\"\n\n\t],\n\n"),
        );
        return $dataset;
    }
}


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


require_once('modules/InboundEmail/InboundEmail.php');

/**
 * @ticket 44009
 */
class Bug44009Test extends Sugar_PHPUnit_Framework_TestCase
{

	protected $ie = null;

	public function setUp()
    {
		$this->ie = new InboundEmail();
	}

    public function getData()
    {
        return array(
            array("test<b>test</b>", "test<b>test</b>"),
            array("<html>test<b>test</b></html>", "test<b>test</b>"),
            array("<html><head></head><body>test<b>test</b></body></html>", "test<b>test</b>"),
            array("<html><head><style>test</style></head><body>test<b>test</b></body></html>", "test<b>test</b>"),
            array("<html><head></head><body><script language=\"javascript\">alert('test!');</script>test<b>test</b></body></html>", "test<b>test</b>"),
            array("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\"><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-1\" /><title>test 12345</title></head><body><p>test<b>test</b></body></html>", "<p>test<b>test</b></p>"),
            );
    }

    /**
     * @dataProvider getData
     * @param string $url
     */
	function testEmailCleanup($data, $res)
	{
        $this->assertEquals($res,SugarCleaner::cleanHtml($data));
	}
}
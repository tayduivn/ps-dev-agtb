<?php
//FILE SUGARCRM flav=PRO only
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

/**
 * ExtAPILotusLiveTest.php
 *
 * This test is for the ExtAPILotusLive.php class and the related functionality towards the Lotus Live web service
 *
 * @author Collin Lee
 *
 */

require_once('tests/include/externalAPI/LotusLive/ExtAPILotusLiveMock.php');

class ExtAPILotusLiveTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        global $app_strings;
      	$app_strings = return_application_language('en_us');
    }

    /**
     * testUploadDocConflictErrorMessage
     *
     * This method tests that we get a unique error message for duplicate upload document conflicts
     *
     */
    public function testUploadDocConflictErrorMessage()
    {
        /*
        $responseMock = $this->getMock('Response', array('getBody', 'getMessage', 'isSuccessful'));
        $responseMock->expects($this->any())
            ->method('isSuccessful')
            ->with($this->any())
            ->will($this->returnValue(false));
        $responseMock->expects($this->any())
            ->method('getBody')
            ->with($this->any())
            ->will($this->returnValue('<?xml version="1.0" encoding="UTF-8"?><lcmis:error xmlns:lcmis="http://www.ibm.com/xmlns/prod/sn/cmis"><lcmis:code>contentAlreadyExists</lcmis:code><lcmis:message>EJPVJ9037E: Unable to add media.</lcmis:message><lcmis:userAction></lcmis:userAction></lcmis:error>'));
        $responseMock->expects($this->any())
            ->method('getMessage')
            ->with($this->any())
            ->will($this->returnValue('Conflict'));

        $clientMock3 = $this->getMock('Client', array('request'));
        $clientMock3->expects($this->any())
            ->method('request')
            ->with($this->any())
            ->will($this->returnValue($responseMock));

        $clientMock2 = $this->getMock('Client', array('setHeaders'));
        $clientMock2->expects($this->any())
            ->method('setHeaders')
            ->with($this->any())
            ->will($this->returnValue($clientMock3));

        $clientMock = $this->getMock('Client', array('setRawData'));
        $clientMock->expects($this->any())
            ->method('setRawData')
            ->with($this->any())
            ->will($this->returnValue($clientMock2));

        $oauthMock = $this->getMock('SugarOauth', array('setUri'));
        $oauthMock->expects($this->any())
            ->method('setUri')
            ->will($this->returnValue($clientMock));

        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        ExtAPILotusLiveMock::$llMimeWhitelist = array();
        $externalAPILotusLiveMock->sugarOauthMock = $oauthMock;

        //$result = $externalAPILotusLiveMock->uploadDoc(new Document(), 'data/SugarBean.php', 'Bug50322Test.doc', 'application/msword');
        */

        $externalAPILotusLiveMock = new ExtAPILotusLiveMock();
        $msg = $externalAPILotusLiveMock->getErrorStringFromCode('Conflict');
        $this->assertEquals('A file with the same name already exists in the system.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode();
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);

        $msg = $externalAPILotusLiveMock->getErrorStringFromCode(array());
        $this->assertEquals('An error occurred when trying to save to the external account.', $msg);
    }

}


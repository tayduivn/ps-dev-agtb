<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
require_once 'include/api/RestService.php';
require_once 'modules/Reports/clients/base/api/ReportsApi.php';

class ReportsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function testGetRecordIdsFromReport_NoAccess_ThrowsException()
    {
        $mockSavedReport = self::getMock("SavedReport", array("ACLAccess"));
        $mockSavedReport->expects(self::once())
            ->method("ACLAccess")
            ->will(self::returnValue(false));


        $mockApiClass =  self::getMock("ReportsApi", array("getSavedReportById"));
        $mockApiClass->expects(self::once())
            ->method("getSavedReportById")
            ->will(self::returnValue($mockSavedReport));

        self::setExpectedException("SugarApiExceptionNotAuthorized");
        SugarTestReflection::callProtectedMethod($mockApiClass, 'getRecordIdsFromReport', array('1234-4567-8888-9999'));
    }

    public function testGetRecordIdsFromReport_NoID_ThrowsException()
    {
        $mockSavedReport = self::getMock("SavedReport", array("ACLAccess"));
        $mockSavedReport->expects(self::once())
            ->method("ACLAccess")
            ->will(self::returnValue(true));

        $mockApiClass =  self::getMock("ReportsApi", array("getSavedReportById"));
        $mockApiClass->expects(self::once())
            ->method("getSavedReportById")
            ->will(self::returnValue($mockSavedReport));

        self::setExpectedException("SugarApiExceptionNotAuthorized");
        SugarTestReflection::callProtectedMethod($mockApiClass, 'getRecordIdsFromReport', array('1234-4567-8888-9999'));
    }

    public function testGetRecordIdsFromReport_NoContent_ReturnsEmptyArray()
    {
        $expectedCount = 0;
        $mockSavedReport = self::getMock("SavedReport", array("ACLAccess", "runReportQuery"));
        $mockSavedReport->expects(self::once())
            ->method("ACLAccess")
            ->will(self::returnValue(true));
        $mockSavedReport->expects(self::never())
            ->method("runReportQuery");

        $mockSavedReport->id = 'ABCD-1234-FEGJD-5678';
        $mockSavedReport->report_type = 'tabular';

        $mockApiClass =  self::getMock("ReportsApi", array("getSavedReportById"));
        $mockApiClass->expects(self::once())
            ->method("getSavedReportById")
            ->will(self::returnValue($mockSavedReport));

        $actualResults = SugarTestReflection::callProtectedMethod($mockApiClass, 'getRecordIdsFromReport', array('1234-4567-8888-9999'));

        self::assertEquals($expectedCount, count($actualResults), "{$expectedCount} records expected in the result from query");
    }

    public function testGetRecordIdsFromReport_ReturnsRecordIdsSuccessfully()
    {
        $id1 = "444e6b6d-2647-7e57-abcd-62dea83c622d";
        $id2 = "222e6b6d-2647-7e57-efgh-72dea83c622b";
        $id3 = "888e6b6d-2647-7e57-ijkl-82dea83c622a";
        $queryResults = array(
            0 => array(
                "primaryid"     => $id1,
                "accounts_name" => "Test1",
            ),
            1 => array(
                "primaryid"     => $id2,
                "accounts_name" => "Test2",
            ),
            2 => array(
                "primaryid"     => $id3,
                "accounts_name" => "Test3",
            ),
        );

        $expectedResults = array(
            $id1,
            $id2,
            $id3,
        );

        $expectedCount = count($expectedResults);
        $mockSavedReport = self::getMock("SavedReport", array("ACLAccess", "runReportQuery"));
        $mockSavedReport->expects(self::once())
            ->method("ACLAccess")
            ->will(self::returnValue(true));
        $mockSavedReport->expects(self::once())
            ->method("runReportQuery")
            ->will(self::returnValue($queryResults));

        $mockSavedReport->id = 'ABCD-1234-FEGJD-5678';
        $mockSavedReport->report_type = 'tabular';
        $mockSavedReport->content =  '{"display_columns":[]}';

        $mockApiClass =  self::getMock("ReportsApi", array("getSavedReportById"));
        $mockApiClass->expects(self::once())
            ->method("getSavedReportById")
            ->will(self::returnValue($mockSavedReport));

        $actualResults = SugarTestReflection::callProtectedMethod($mockApiClass, 'getRecordIdsFromReport', array('1234-4567-8888-9999'));

        self::assertEquals($expectedCount, count($actualResults), "{$expectedCount} records expected in the result from query");
    }
}

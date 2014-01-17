<?php

require_once('modules/Reports/clients/base/api/ReportsDashletsApi.php');

/**
 * RS-139: Prepare ReportsDashlets Api
 */
class RS139Test extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var ReportsDashletsApi */
    protected $api = null;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));
        SugarTestHelper::setUp('app_list_strings');

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new ReportsDashletsApi();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Test assserts that getSavedReports returns data
     */
    public function testGetSavedReports()
    {
        $args = array();
        $actual = $this->api->getSavedReports($this->service, $args);
        $this->assertNotEmpty($actual);
    }

    /**
     * Test assserts that getSavedReports with has_charts flag returns data
     */
    public function testGetSavedReportsHasChart()
    {
        $args = array(
            'has_charts' => 'true',
        );
        $actual = $this->api->getSavedReports($this->service, $args);
        $this->assertNotEmpty($actual);

        return reset($actual);
    }

    /**
     * Test asserts that testGetSavedReportChartById returns data for report with chart
     *
     * @depends testGetSavedReportsHasChart
     */
    public function testGetSavedReportChartById($report)
    {
        $args = array(
            'reportId' => $report['id'],
        );
        $actual = $this->api->getSavedReportChartById($this->service, $args);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('chartData', $actual);
        $this->assertArrayHasKey('reportData', $actual);
    }
}

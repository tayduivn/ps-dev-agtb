<?php

// THEBEARD: This needs to extend the base abstractForecast Module not the manager
abstract class SugarForecasting_Chart_AbstractChart extends SugarForecasting_Manager
{
    /**
     * X-Axis Label
     *
     * @var string
     */
    protected $xaxisLabel = 'Amount';

    /**
     * Y-Axis Label
     *
     * @var string
     */
    protected $yaxisLabel = '';

    /**
     * Pareto Label
     *
     * @var string
     */
    protected $goalParetoLabel = '';

    /**
     * Which field we need to pull in to the manager chart from the forecast worksheet
     *
     * @var string
     */
    protected $managerAdjustedField = 'likely_adjusted';

    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        parent::__construct($args);
    }

    /**
     * Setup the report builder for use
     *
     * @return ReportBuilder
     */
    protected function setUpReportBuilder()
    {
        // get the report defs and filters from the dataClass
        $report_defs = $this->dataClass->getWorksheetDefinition('Opportunities');
        $filters = $this->dataClass->getFilters($this->getArgs());

        $rb = $this->generateReportBuilder('Opportunities', $report_defs[2], $filters);
        $rb = $this->processDataset($rb);

        return $rb;
    }

    /**
     * @param ReportBuilder $rb
     * @return Array
     * @throws Exception
     */
    protected function generateChartDataArray(ReportBuilder $rb)
    {

        $chart_contents = $rb->toJson();

        //Get the goal marker values
        require_once("include/SugarCharts/ChartDisplay.php");
        // create the chart display engine
        $chartDisplay = new ChartDisplay();
        // set the reporter with the chart contents from the report builder
        $chartDisplay->setReporter(new Report($chart_contents));

        // if we can't draw the chart, kick it back
        if ($chartDisplay->canDrawChart() === false) {
            // no chart to display, so lets just kick back the error message
            global $current_language;
            $mod_strings = return_module_language($current_language, 'Reports');
            throw new Exception($mod_strings['LBL_NO_CHART_DRAWN_MESSAGE']);
        }

        // lets get some json!
        $json = $chartDisplay->generateJson();

        return json_decode($json, true);
    }

    abstract protected function getQuota();

    protected function fixGroupByLabels($dataArray)
    {
        global $forecast_strings;
        $args = $this->getArgs();
        if (isset($args['group_by'])) {
            if ($args['group_by'] == "forecast" && isset($dataArray['label'][0])) {
                // fix the labels
                $dataArray['label'][0] = ($dataArray['label'][0] == 0) ? $forecast_strings['LBL_CHART_NOT_INCLUDED'] : $forecast_strings['LBL_CHART_INCLUDED'];
                if (isset($dataArray['label'][1])) {
                    $dataArray['label'][1] = ($dataArray['label'][1] == 0) ? $forecast_strings['LBL_CHART_NOT_INCLUDED'] : $forecast_strings['LBL_CHART_INCLUDED'];
                }
            } else if ($args['group_by'] == "probability") {
                foreach ($dataArray['label'] as $key => $value) {
                    $dataArray['label'][$key] = $value . '%';
                }
            }
        }

        return $dataArray;
    }
}
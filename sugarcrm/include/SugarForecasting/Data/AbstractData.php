<?php

abstract class SugarForecasting_Data_AbstractData implements SugarForecasting_Data_DataInterface
{
    /**
     * @var array
     */
    protected $def = array();

    /**
     * Return any filters specific to this data interface
     *
     * @param array $args       The Arguments from the REST api
     * @return array
     */
    public function getFilters($args)
    {
        return array();
    }

    /**
     * Return any filters specific to the chart to this data interface
     *
     * @param array $args
     * @return array
     */
    public function getChartFilters($args)
    {
        return array();
    }

    /**
     * Returns the Report's module JSON encoded format chart definition as a String.  Implementations should have a definition
     * created in the format of the Reports module and return this report definition string.
     *
     * @param string $id            Optional string id in the event there may be multiple worksheet data definitions
     * @return mixed
     */
    public function getWorksheetDefinition($id='')
    {
        return isset($this->def[$id]) ? $this->def[$id] : array();
    }
}
<?php

interface SugarForecasting_Chart_ChartInterface
{
    /**
     * This is used to run all the commands that are need to get a chart back out of the system
     *
     * @return string|array
     */
    public function process();
}

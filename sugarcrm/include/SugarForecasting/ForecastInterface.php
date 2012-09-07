<?php

interface SugarForecasting_ForecastInterface
{
    /**
     * This is used to run all the commands that are need to get a forecast back out of the system
     *
     * @return string|array
     */
    public function process();
}

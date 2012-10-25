<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/ForecastProcessInterface.php');
require_once('include/SugarForecasting/AbstractForecastArgs.php');
abstract class SugarForecasting_Export_AbstractExport extends SugarForecasting_AbstractForecastArgs implements SugarForecasting_ForecastProcessInterface
{

    /**
     * Are we a manager
     *
     * @var bool
     */
    protected $isManager = false;

    /**
     * Where we store the data we want to use
     *
     * @var array
     */
    protected $dataArray = array();

    /**
     * Class Constructor
     * @param array $args       Service Arguments
     */
    public function __construct($args)
    {
        if(!empty($args['dataset'])) {
            $this->dataset = $args['dataset'];
        }

        parent::__construct($args);
    }

    /**
     * Return the data array
     *
     * @return array
     */
    public function getDataArray()
    {
        return $this->dataArray;
    }


    /**
     * getFilename
     *
     * @return string name of the filename to export contents into
     */
    protected function getFilename()
    {
        if(!isset($this->args['timeperiod_id']) || !isset($this->args['user_id']))
        {
            global $current_user;
            $timedate = TimeDate::getInstance();
            $timeStamp = $timedate->asUserTs($timedate->getNow(), $current_user);
            return $timeStamp . '.csv';
        }

        $timePeriod = BeanFactory::getBean('TimePeriods');
        $timePeriod->retrieve($this->args['timeperiod_id']);
        $filename = sprintf("%s_to_%s_%s_%s.csv", $timePeriod->start_date, $timePeriod->end_date, $this->args['user_id'], $this->isManager ? 'manager' : 'individual');
        return $filename;
    }


    /**
     * export
     *
     * @param $contents String value of the file contents
     */
    public function export($contents)
    {
        global $locale;
        $filename = $this->getFilename();
        header("Content-Disposition: attachment; filename={$filename}");
        header("Content-Type: text/x-csv");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . TimeDate::httpTime() );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".mb_strlen($locale->translateCharset($contents, 'UTF-8', $locale->getExportCharset())));
        echo $locale->translateCharset($contents, 'UTF-8', $locale->getExportCharset());
    }
}

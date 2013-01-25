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
     * getContent
     *
     * Returns all the content for the export
     *
     * @param $data
     * @param $focus
     * @param $fields_array
     *
     * @return string content for the export file
     */
    protected function getContent($data, $focus, $fields_array)
    {
        require_once('include/export_utils.php');
        $fields_array = get_field_order_mapping($focus->object_name, $fields_array);

        foreach($fields_array as $key=>$label)
        {
             $fields_array[$key] = translateForExport($label, $focus);
        }

        // setup the "header" line with proper delimiters
        $content = "\"".implode("\"".getDelimiter()."\"", array_values($fields_array))."\"\r\n";

        if(!empty($data))
        {
            $content .= $this->getExportDataContent($data, $focus, $fields_array);
        }

        return $content;
    }

    /**
     * getExportDataContent
     *
     * Returns the export content for the data portion
     *
     * @param $data
     * @param $focus
     * @param $fields_array
     *
     * @return string content for the data portion of export
     */
    protected function getExportDataContent($data, $focus, $fields_array)
    {
        require_once('include/SugarFields/SugarFieldHandler.php');
        require_once('include/export_utils.php');

        global $current_user;
        $content = '';
        $delimiter = getDelimiter();

        //process retrieved record
        //BEGIN SUGARCRM flav=pro ONLY
        $isAdminUser = is_admin($current_user);
        //END SUGARCRM flav=pro ONLY

        foreach($data as $val)
        {

            $new_arr = array();

            //BEGIN SUGARCRM flav=pro ONLY
            if(!$isAdminUser)
            {
                $focus->id = (!empty($val['id']))?$val['id']:'';
                $focus->assigned_user_id = (!empty($val['assigned_user_id']))?$val['assigned_user_id']:'' ;
                $focus->created_by = (!empty($val['created_by']))?$val['created_by']:'';
                $focus->ACLFilterFieldList($val, array(), array("blank_value" => true));
            }
            //END SUGARCRM flav=pro ONLY

            foreach ($fields_array as $key => $label)
            {
                $value = $val[$key];

                //getting content values depending on their types
                if(isset($focus->field_defs[$key]))
                {
                    $sfh = SugarFieldHandler::getSugarField($focus->field_defs[$key]['type']);
                    $value = $sfh->exportSanitize($value, $focus->field_defs[$key], $focus, $val);
                }

                $new_arr[$key] = preg_replace("/\"/", "\"\"", $value);
            }

            $line = implode("\"". $delimiter ."\"", $new_arr);
            $content .= "\"" . $line . "\"\r\n";
        }

        return $content;
    }


    /**
     * getFilename
     *
     * @return string name of the filename to export contents into
     */
    public function getFilename() {
        $timePeriod = BeanFactory::getBean('TimePeriods');
        $timePeriod->retrieve($this->args['timeperiod_id']);
        return $timePeriod->name;
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
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header("Content-Type: text/x-csv");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . TimeDate::httpTime() );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".mb_strlen($locale->translateCharset($contents, 'UTF-8', $locale->getExportCharset())));
        echo $locale->translateCharset($contents, 'UTF-8', $locale->getExportCharset());
    }
}

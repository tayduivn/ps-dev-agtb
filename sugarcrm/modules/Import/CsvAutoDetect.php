<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2011 SugarCRM, Inc.; All Rights Reserved.
 *********************************************************************************/

/*********************************************************************************
 * Description: Class to detect csv file settings (delimiter, enclosure, etc
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/

require_once('include/parsecsv.lib.php');

class CsvAutoDetect {

    protected $_parser = null;

    protected $_csv_file = null;

    protected $_max_depth = 15;

    protected $_parsed = false;

    static protected $_date_formats = array(
        '12/23/2010' => "/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])\/\d\d\d\d/",
        '23/12/2010' => "/(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012])\/\d\d\d\d/",
        '2010/12/23' => "/\d\d\d\d\/(0[1-9]|1[012])\/(0[1-9]|[12][0-9]|3[01])/",
        '12-23-2010' => "/(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])-\d\d\d\d/",
        '23-12-2010' => "/(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[012])-\d\d\d\d/",
        '2010-12-23' => "/\d\d\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])/",
        '12.23.2010' => "/(0[1-9]|1[012])\.(0[1-9]|[12][0-9]|3[01])\.\d\d\d\d/",
        '23.12.2010' => "/(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[012])\.\d\d\d\d/",
        '2010.12.23' => "/\d\d\d\d\.(0[1-9]|1[012])\.(0[1-9]|[12][0-9]|3[01])/"
    );

    static protected $_time_formats =  array(
        '11:00:00pm' => "/(0[0-9]|1[0-2]):([0-5][0-9]):([0-5][0-9])[am|pm]/",
        '11:00:00PM' => "/(0[0-9]|1[0-2]):([0-5][0-9]):([0-5][0-9])[AM|PM]/",
        '11:00:00 pm' => "/(0[0-9]|1[0-2]):([0-5][0-9]):([0-5][0-9]) [am|pm]/",
        '11:00:00 PM' => "/(0[0-9]|1[0-2]):([0-5][0-9]):([0-5][0-9]) [AM|PM]/",
        '23:00:00' => "/(0[0-9]|1[0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])/",
        '11.00.00pm' => "/(0[0-9]|1[0-2])\.([0-5][0-9])\.([0-5][0-9])[am|pm]/",
        '11.00.00PM' => "/(0[0-9]|1[0-2])\.([0-5][0-9])\.([0-5][0-9])[AM|PM]/",
        '11.00.00 pm' => "/(0[0-9]|1[0-2])\.([0-5][0-9])\.([0-5][0-9]) [am|pm]/",
        '11.00.00 PM' => "/(0[0-9]|1[0-2])\.([0-5][0-9])\.([0-5][0-9]) [AM|PM]/",
        '23.00.00' => "/(0[0-9]|1[0-9]|2[0-4])\.([0-5][0-9])\.([0-5][0-9])/"
    );


    /**
     * Constructor
     *
     * @param string $csv_filename
     * @param int $max_depth
     */
    public function __construct($csv_filename, $max_depth = 15) {
        $this->_csv_file = $csv_filename;

        $this->_parser = new parseCSV();

        $this->_parser->auto_depth = $max_depth;

        $this->_max_depth = $max_depth;
    }


    /**
     * To get the possible csv settings (delimiter, enclosure, heading)
     *
     * @param string $delimiter
     * @param string $enclosure
     * @param bool $heading
     * @return bool true if settings are found, false otherwise
     */
    public function getCsvSettings(&$delimiter, &$enclosure, &$heading) {
        // try parsing the file to find possible delimiter and enclosure
        $this->_parser->heading = false;

        $enclosures = array("\"", "'");

        $found_setting = false;

        foreach ($enclosures as $enc) {
            $delimiter = $this->_parser->auto($this->_csv_file, true, null, null, $enc);

            if (strlen($delimiter) == 1) {
                // possible delimiter and enclosure found
                $enclosure = $enc;
                $found_setting = true;
                break;
            }
        }

        if (!$found_setting) {
            return false;
        }

        // checking heading
        $heading = true;
        foreach ($this->_parser->data[0] as $val) {
            // if it contains number, then it's probably not a header
            // this can be very unreliable, but...
            $ret = preg_match("/[0-9]/", $val);
            if ($ret) {
                $heading = false;
                break;
            }
        }

        $this->_parsed = true;

        return true;
    }


    /**
     * To get the possible format (for date or time)
     *
     * @param array $formats
     * @return mixed possible format if found, false otherwise
     */
    protected function getFormat(&$formats) {

        if (!$this->_parsed) {
            return false;
        }

        $depth = 1;

        foreach ($this->_parser->data as $row) {

            foreach ($row as $val) {

                foreach ($formats as $format=>$regex) {

                    $ret = preg_match($regex, $val);
                    if ($ret) {
                        return $format;
                    }
                }
            }

            // give up if reaching max depth
            $depth++;
            if ($depth > $this->_max_depth) {
                break;
            }
        }

        return false;
    }


    /**
     * To get the possible date format used in the csv file
     *
     * @return mixed possible date format if found, false otherwise
     */
    public function getDateFormat() {

        $format = $this->getFormat(self::$_date_formats);

        return $format;
    }


    /**
     * To get the possible time format used in the csv file
     *
     * @return mixed possible time format if found, false otherwise
     */
    public function getTimeFormat() {

        $format = $this->getFormat(self::$_time_formats);

        return $format;
    }

}
?>

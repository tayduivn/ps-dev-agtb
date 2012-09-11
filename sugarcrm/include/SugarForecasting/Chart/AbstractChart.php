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

require_once('include/SugarForecasting/Chart/ChartInterface.php');
require_once('include/SugarForecasting/AbstractForecastArgs.php');
abstract class SugarForecasting_Chart_AbstractChart extends SugarForecasting_AbstractForecastArgs implements SugarForecasting_Chart_ChartInterface
{
    /**
     * Which data set are we working with?
     *
     * @var string
     */
    protected $dataset = 'likely';

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
     * The default properties that are passed back for the Chart
     *
     * @var array
     */
    protected $defaultPropertiesArray = array(
        'gauge_target_list' => 'Array',
        'title' => NULL,
        'subtitle' => '',
        'type' => 'bar chart',
        'legend' => 'on',
        'labels' => 'value',
        'print' => 'on',
        'thousands' => '',
        'goal_marker_type' =>
        array(
            0 => 'group',
            1 => 'pareto',
        ),
        'goal_marker_color' =>
        array(
            0 => '#3FB300',
            1 => '#7D12B2',
        ),
        'goal_marker_label' =>
        array(
            0 => 'Quota',
            1 => '',
        ),
        'label_name' => '',
        'value_name' => '',
    );

    /**
     * Default Colors
     *
     * @var array
     */
    protected $defaultColorsArray = array(
        0 => '#8c2b2b',
        1 => '#468c2b',
        2 => '#2b5d8c',
        3 => '#cd5200',
        4 => '#e6bf00',
        5 => '#7f3acd',
        6 => '#00a9b8',
        7 => '#572323',
        8 => '#004d00',
        9 => '#000087',
        10 => '#e48d30',
        11 => '#9fba09',
        12 => '#560066',
        13 => '#009f92',
        14 => '#b36262',
        15 => '#38795c',
        16 => '#3D3D99',
        17 => '#99623d',
        18 => '#998a3d',
        19 => '#994e78',
        20 => '#3d6899',
        21 => '#CC0000',
        22 => '#00CC00',
        23 => '#0000CC',
        24 => '#cc5200',
        25 => '#ccaa00',
        26 => '#6600cc',
        27 => '#005fcc',
    );

    /**
     * What the default chart value array looks like
     *
     * @var array
     */
    protected $defaultValueArray = array(
        'label' => '',
        'gvalue' => 0,
        'gvaluelabel' => 0,
        'values' => array(),
        'valuelabels' => array(),
        'links' => array(),
        'goalmarkervalue' => array(),
        'goalmarkervaluelabel' => array()
    );

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
}

<?php

abstract class SugarForecasting_Chart_AbstractChart
{
    /**
     * Which data set are we working with?
     *
     * @var string
     */
    protected $dataset = 'likely';

    /**
     * @var array Rest Arguments
     */
    protected $args;

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

        $this->setArgs($args);
    }

    /**
     * Set the arguments
     *
     * @param array $args
     * @return SugarForecasting_AbstractForecast
     */
    public function setArgs($args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Return the arguments array
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Get a specific Arg Value, If it doesn't exist return Empty
     *
     * @param $key
     * @return string
     */
    public function getArg($key)
    {
        return isset($this->args[$key]) ? $this->args[$key] : "";
    }

    /**
     * Set an Arg to track
     *
     * @param string $key
     * @param mixed $value
     * @return SugarForecasting_AbstractForecast
     */
    public function setArg($key, $value)
    {
        $this->args[$key] = $value;

        return $this;
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
     * Method for sorting the dataArray before we return it so that the tallest bar is always first and the
     * lowest bar is always last.
     *
     * @param array $a          The left side of the compare
     * @param array $b          The right side of the compare
     * @return int
     */
    protected function sortChartColumns($a, $b)
    {
        if (intval($a['gvalue']) > intval($b['gvalue'])) {
            return -1;
        } else if (intval($a['gvalue']) < intval($b['gvalue'])) {
            return 1;
        } else {
            return 0;
        }
    }
}

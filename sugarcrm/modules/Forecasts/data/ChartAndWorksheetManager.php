<?php

class ChartAndWorksheetManager {

private $dataMapping;
private $dataInstances;

private function ChartAndWorksheetManager()
{
    //first check if a custom file exists
    //there should be an Array named $chartAndWorksheetMapping if you decide on this course
    if(file_exists('custom/modules/Forecasts/data/config.php'))
    {
        require($configFile);
        $this->dataMapping = $chartAndWorksheetMapping;
    } else {
        //Default settings
        $this->dataMapping = array(
            'manager' => 'modules/Forecasts/data/manager/Manager.php',
            'individual' => 'modules/Forecasts/data/individual/Individual.php',
        );
    }
}

public static final function getInstance()
{
    static $instance;
    if(is_null($instance))
    {
       $instance = new ChartAndWorksheetManager();
    }
    return $instance;
}


/**
 * @param $type
 * @param string $id
 * @return mixed
 */
public function getChartDefintion($type, $id='')
{
    try {
      $instance = $this->getDataInstance($type);
      return $instance->getChartDefinition($id);
    } catch (Exception $ex) {
      return null;
    }
}


/**
 * @param $type
 * @param string $id
 * @return mixed
 */
public function getWorksheetDefintion($type, $id='')
{
    try {
      $instance = $this->getDataInstance($type);
      return $instance->getWorksheetDefinition($id);
    } catch (Exception $ex) {
      return null;
    }
}

/**
 *
 * @param $type String value of the data type to retrieve (ex: 'manager', 'individual')
 * @return IChartAndWorksheet Interface instance
 *
 * @throws Exception
 */
protected function getDataInstance($type)
{
    if(isset($this->instances[$type]))
    {
       return $this->dataInstances[$type];
    }

    if(!isset($this->dataMapping[$type]))
    {
       throw new Exception("Undefined type: [{$type}] in data mapping.");
    }

    $klass = ucfirst($type);

    require_once($this->dataMapping[$type]);
    return new $klass();
}

}
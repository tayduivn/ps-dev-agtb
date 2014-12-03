<?php

/**
 * Interface to save pre/post variables & values data from Modules forms changes into a temporaly array.
 *
 */
class PMSEHistoryData
{
    /**
     * Log data contains module name, fields pre data and fields post data.
     * @var array
     */
    private $log_data = array();

    /**
     * If is repeated
     * @var boolean
     */
    private $repeated = false;

    /**
     * If is lock
     * @var boolean
     */
    private $lock = false;

    /**
     * Setting log_data object, repeated and lock false too for initialize the object .
     * @param string $module
     */
    public function __construct($module)
    {
        $this->log_data = array(
            'module' => $module,
            'before_data' => array(),
            'after_data' => array()
        );
        $this->repeated = false;
        $this->lock = false;
    }

    /**
     * That method prepares log_data before save.
     * @param string $key
     * @param string $value
     */
    public function savePredata($key, $value)
    {
        if ($this->repeated == false && $this->lock == false) {
            $this->log_data['before_data'][$key] = $value;
        }
    }

    /**
     * That method prepares log_data after save.
     * @param string $key
     * @param string $value
     */
    public function savePostData($key, $value)
    {
        if ($this->repeated == false && $this->lock == false) {
            $this->log_data['after_data'][$key] = $value;
        }
    }

    /**
     * That method obtain the log_data property.
     * @return multitype:
     */
    public function getLog()
    {
        return $this->log_data;
    }

    /**
     * That method verifies if the new value is repeated, make a comparison with old value.
     * @param string $oldValue
     * @param string $newValue
     */
    public function verifyRepeated($oldValue, $newValue)
    {
        $this->repeated = false;
        if ($oldValue == $newValue) {
            $this->repeated = true;
        }
    }

    /**
     * That method sets to lock property with a condition value.
     * @param boolean $condition
     */
    public function lock($condition)
    {
        $this->lock = $condition;
    }
}

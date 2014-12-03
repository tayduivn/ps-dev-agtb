<?php

/**
 * Define a custom exception class
 */
class PMSEElementException extends Exception
{
    protected $flowData;
    protected $element;

    // Redefine the exception so message isn't optional
    public function __construct($message, $flowData, $element, $code = 0, Exception $previous = null)
    {
        // some code
        $flowData['cas_flow_status'] = 'ERROR';
        $this->flowData = $flowData;
        $this->element = $element;
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getFlowData()
    {
        return $this->flowData;
    }

    public function getElement()
    {
        return $this->element;
    }
}
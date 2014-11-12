<?php

require_once 'PMSEShape.php';
require_once 'modules/pmse_Inbox/engine/PMSEExpressionEvaluator.php';

class PMSEGateway extends PMSEShape
{
    protected $expressionEvaluator;

    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getExpressionEvaluator()
    {
        return $this->expressionEvaluator;
    }

    /**
     * 
     * @param type $expressionEvaluator
     * @codeCoverageIgnore
     */
    public function setExpressionEvaluator($expressionEvaluator)
    {
        $this->expressionEvaluator = $expressionEvaluator;
    }

    /**
     * Class Constructor
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->expressionEvaluator = new PMSEExpressionEvaluator();
    }

}

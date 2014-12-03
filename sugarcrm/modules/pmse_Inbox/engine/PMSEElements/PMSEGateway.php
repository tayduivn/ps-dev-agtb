<?php
require_once 'PMSEShape.php';
require_once 'modules/pmse_Inbox/engine/PMSEEvaluator.php';

class PMSEGateway extends PMSEShape
{
    protected $evaluator;

    /**
     *
     * @return type
     * @codeCoverageIgnore
     */
    public function getEvaluator()
    {
        return $this->evaluator;
    }

    /**
     *
     * @param type $expressionEvaluator
     * @codeCoverageIgnore
     */
    public function setEvaluator($expressionEvaluator)
    {
        $this->evaluator = $expressionEvaluator;
    }

    /**
     * Class Constructor
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();
        $this->evaluator = new PMSEEvaluator();
    }

}

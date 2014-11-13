<?php
/**
 * Class that takes a parser expression to be evaluated and then return a value true or false
 * the expression to be entered in json
 *
 */
require_once 'PMSEEvalCriteria.php';
require_once 'parser/PMSEDataParserGateway.php';

class PMSEExpressionEvaluator
{
    /**
     * Object of the class ADAMEvalCriteria
     * @var object
     */
    protected $evaluator;

    /**
     * Object of the class ADAMDataParserGateway
     * @var object
     */
    protected $parser;

    /**
     * Constructor
     * initialize variables with classes parser and evaluation
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->evaluator = new PMSEEvalCriteria();
        $this->parser = new PMSEDataParserGateway();
    }

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
     * @return type
     * @codeCoverageIgnore
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * 
     * @param type $evaluator
     * @codeCoverageIgnore
     */
    public function setEvaluator($evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * 
     * @param type $parser
     * @codeCoverageIgnore
     */
    public function setParser($parser)
    {
        $this->parser = $parser;
    }
        
    /**
     * Parsing and evaluation of expression
     * the expression is in json
     * @global object $current_user this is the current user object
     * @global object $beanList list of all modules of sugar
     * @param json $expression expression to evaluate
     * @param object $evaluatedBean this is the bean object
     * @param array $params if additional parameters
     * @return bool
     */
    public function evaluateExpression($expression, $evaluatedBean, $params = array())
    {
        global $current_user;
        global $beanList;
        $expression = json_decode(html_entity_decode($expression));
        if (isset($params['replace_fields']) && !empty($params['replace_fields'])) {
            foreach ($expression as $expKey => $expVal) {
                foreach ($expVal as $attrKey => $attrVal) {
                    foreach ($params['replace_fields'] as $fieldKey => $fieldVal) {
                        if ($attrVal == $fieldKey) {
                            $expression[$expKey]->$attrKey = $fieldVal;
                        }
                    }
                }
            }
        }

        $parsedArray = $this->parser->parseCriteriaArray($expression, 
                                                         $evaluatedBean, 
                                                         $current_user, 
                                                         $beanList, 
                                                         $params);

        $result = $this->evaluator->expresions($parsedArray);
        return $result;
    }

    /**
     * Returns the operations performed with evaluation
     * @return string contains what I evaluated
     * @codeCoverageIgnore
     */
    public function condition()
    {
        return $this->evaluator->condition();
    }
}

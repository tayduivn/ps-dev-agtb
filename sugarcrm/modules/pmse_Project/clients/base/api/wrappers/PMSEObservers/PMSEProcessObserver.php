<?php


require_once 'PMSEObserver.php';
require_once 'include/SugarQuery/SugarQuery.php';
require_once 'modules/pmse_Inbox/engine/PMSELogger.php';
require_once 'modules/pmse_Project/clients/base/api/wrappers/PMSERelatedDependencyWrapper.php';

/**
 * Description of PMSEProcessObserver
 *
 */
class PMSEProcessObserver implements PMSEObserver
{

    /**
     *
     * @var type 
     */
    protected $sugarQuery;

    /**
     *
     * @var PMSELogger
     */
    protected $logger;
    
    /**
     * 
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->sugarQuery = new SugarQuery();
        $this->logger = PMSELogger::getInstance();
    }
    
    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getSugarQuery()
    {
        return $this->sugarQuery;
    }

    /**
     * 
     * @return type
     * @codeCoverageIgnore
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * 
     * @param type $sugarQuery
     * @codeCoverageIgnore
     */
    public function setSugarQuery($sugarQuery)
    {
        $this->sugarQuery = $sugarQuery;
    }

    /**
     * 
     * @param PMSELogger $logger
     * @codeCoverageIgnore
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
        
    /**
     * 
     * @param type $id
     * @return type
     * @codeCoverageIgnore
     */
    public function getRelatedDependencyBean($id = null)
    {
        return BeanFactory::getBean('pmse_BpmRelatedDependency', $id);
    }
    
    /**
     * 
     * @param PMSEObservable $subject
     * @return type
     */
    public function update($subject)
    {
        if (method_exists($subject, 'getProcessDefinition')) {
            $this->logger->debug("Trigger update of a Related Relationship for a Process Definition update");

            $processDefinition = $subject->getProcessDefinition();
            $processDefinitionData = $processDefinition->fetched_row;

            $fields = array(
                'id',
            );

            $relatedDependency = $this->getRelatedDependencyBean();

            $this->sugarQuery->select($fields);
            $this->sugarQuery->from($relatedDependency);
            $this->sugarQuery->where()->queryAnd()
                    ->addRaw("pro_id='{$processDefinitionData['id']}' AND prj_id='{$processDefinitionData['prj_id']}' AND deleted=0");

            $result = $this->sugarQuery->compileSql();
            $this->logger->debug("Retrieve dependencies query: {$result}");

            $rows = $this->sugarQuery->execute();
            foreach ($rows as $row) {
                $bean = $this->getRelatedDependencyBean($row['id']);
                $bean->pro_status = $processDefinitionData['pro_status'];
                $bean->pro_locked_variables = $processDefinitionData['pro_locked_variables'];
                $bean->pro_terminate_variables = $processDefinitionData['pro_terminate_variables'];
                if ($bean->pro_module !== $processDefinitionData['pro_module'] && $row['rel_element_type'] == 'TERMINATE'){
                    $bean->deleted = TRUE;
                }
                $bean->save();
            }
            
            $fakeEventData = array(
                'id' => 'TERMINATE',
                'evn_type' => 'GLOBAL_TERMINATE',
                'evn_criteria' => $processDefinitionData['pro_terminate_variables'],
                'evn_behavior' => 'CATCH',
                'pro_id' => $processDefinitionData['id']
            );
            
            $depWrapper = new PMSERelatedDependencyWrapper();
            $depWrapper->processRelatedDependencies($fakeEventData);
            $depNumber = count($rows);
            $this->logger->debug("Updating {$depNumber} dependencies");
        }
        return $result;
    }
}

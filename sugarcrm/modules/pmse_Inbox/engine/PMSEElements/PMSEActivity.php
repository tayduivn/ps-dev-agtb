<?php

require_once 'modules/pmse_Inbox/engine/PMSEHistoryData.php';
require_once 'PMSEShape.php';


/**
 * Description of PMSEActivity
 *
 * @codeCoverageIgnore
 */

class PMSEActivity extends PMSEShape
{
    /**
     *
     * @var type 
     */
    protected $definitionBean;
    
    /**
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->definitionBean = BeanFactory::getBean('pmse_BpmActivityDefinition');
        parent::__construct();
    }
    
    /**
     * 
     * @param type $module
     * @return \PMSEHistoryData
     * @codeCoverageIgnore
     */
    protected function retrieveHistoryData($module)
    {
        return new PMSEHistoryData($module);
    }

    /**
     * @param null $id
     * @return Lead|Opportunity|pmse_Inbox|Team|User
     * @codeCoverageIgnore
     */
    protected function retrieveUserData($id = null)
    {
        return BeanFactory::getBean('Users', $id);
    }

    /**
     * @param $id
     * @return Lead|Opportunity|pmse_Inbox|Team|User
     * @codeCoverageIgnoreØ
     */
    protected function retrieveTeamData($id)
    {
        return BeanFactory::getBean('Teams', $id);
    }

    /**
     * 
     * @param type $id
     * @return type
     * @codeCoverageIgnore
     */
    protected function retrieveDefinitionData($id)
    {
        $this->definitionBean->retrieve($id);
        return $this->definitionBean->fetched_row;
    }
}

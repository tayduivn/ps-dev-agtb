<?php
require_once('PMSEImporter.php');
require_once('PMSEEngineUtils.php');
/**
 * Imports a record of encrypted file.
 *
 * This class extends the class ADAMImporter to import
 * records to an encrypted file BPMRuleSet table.
 * @package PMSE
 * @codeCoverageIgnore
 */
class PMSEBusinessRuleImporter extends PMSEImporter
{

    public function __construct()
    {
        $this->bean = BeanFactory::getBean('pmse_Business_Rules'); //new BpmRuleSet();
        $this->name = 'name';
        $this->id = 'rst_id';
        $this->suffix = 'rst_';
    }

    /**
     * Method to save record in database
     * @param $projectData
     * @return bool
     */
    public function saveProjectData($projectData)
    {
        $source_definition = json_decode($projectData['rst_source_definition']);
        if (isset($projectData[$this->suffix . 'name']) && !empty($projectData[$this->suffix . 'name'])) {
            $name = $this->getNameWhitSuffix($projectData[$this->suffix . 'name']);
        } else {
            $name = $this->getNameWhitSuffix($projectData[$this->name]);
        }
        $projectData['rst_uid'] = PMSEEngineUtils::generateUniqueID();
        $source_definition->name = $name;
        $source_definition->id = $projectData['rst_uid'];
        $projectData['rst_source_definition'] = json_encode($source_definition);
        unset($projectData[$this->id]);
        return parent::saveProjectData($projectData);
    }
}
